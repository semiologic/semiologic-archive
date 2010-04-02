/**
 * Sanitizes an order line's coupon.
 *
 * To force a refresh of an order's details (e.g. when changing its
 * campaign, affiliate, or coupon), update an order line and set the
 * relevant amount fields to NULL.
 */
CREATE OR REPLACE FUNCTION order_lines_sanitize_amounts()
	RETURNS trigger
AS $$
DECLARE
	o			record;
	p			record;
	c			record;
	cur_orders	float;
	t_ratio		numeric := 1;
	o_ratio		numeric := 1;
BEGIN
	IF	TG_OP = 'UPDATE' AND
		NEW.init_amount IS NOT NULL AND NEW.rec_amount IS NOT NULL AND
		NEW.init_comm IS NOT NULL AND NEW.rec_comm IS NOT NULL AND
		NEW.init_discount IS NOT NULL AND NEW.rec_discount IS NOT NULL
	THEN
		IF	NEW.product_id IS NOT NULL AND
			NEW.product_id IS DISTINCT FROM OLD.product_id
		THEN
			-- Validate product
			IF	NOT EXISTS (
				SELECT	1
				FROM	products
				WHERE	id = NEW.product_id
				AND		status > 'draft'
				)
			THEN
				RAISE EXCEPTION 'Cannot tie inactive products.id = % to order_lines.id = %.',
					NEW.product_id, NEW.id;
			END IF;
		END IF;

		IF	NEW.coupon_id IS NOT NULL AND
			NEW.coupon_id IS DISTINCT FROM OLD.coupon_id
		THEN
			-- Validate coupon
			IF	NOT EXISTS (
				SELECT	1
				FROM	campaigns
				WHERE	id = NEW.coupon_id
				AND		status > 'draft'
				)
			THEN
				RAISE EXCEPTION 'Cannot tie inactive campaigns.id = % to order_lines.id = %.',
					NEW.coupon_id, NEW.id;
			END IF;
		END IF;

		IF	ROW(NEW.init_comm, NEW.rec_comm) <> ROW(0,0) AND
			( ROW(NEW.init_amount, NEW.init_comm) IS DISTINCT FROM ROW(OLD.init_amount, OLD.init_comm) OR
			ROW(NEW.rec_amount, NEW.rec_comm) IS DISTINCT FROM ROW(OLD.rec_amount, OLD.rec_comm) )
		THEN
			IF	NOT EXISTS (
				SELECT	1
				FROM	orders
				WHERE	order_id = NEW.order_id
				AND		aff_id IS NOT NULL
				)
			THEN
				NEW.init_comm := 0;
				NEW.rec_comm := 0;
			END IF;
		END IF;
		
		RETURN NEW;
	END IF;
	
	IF	NEW.product_id IS NULL
	THEN
		-- Try to bail early
		NEW.init_discount := COALESCE(NEW.init_discount, 0);
		NEW.rec_discount := COALESCE(NEW.rec_discount, 0);
		NEW.init_amount := COALESCE(NEW.init_amount, 0);
		NEW.rec_amount := COALESCE(NEW.rec_amount, 0);
		NEW.init_comm := LEAST(COALESCE(NEW.init_comm, 0), NEW.init_amount);
		NEW.rec_comm := LEAST(COALESCE(NEW.rec_comm, 0), NEW.rec_amount);
	
		IF	NEW.order_id IS NOT NULL AND
			ROW(NEW.init_comm, NEW.rec_comm) = ROW(0, 0)
		THEN
			-- Nothing to do or verify
			NEW.coupon_id := NULL;
			RETURN NEW;
		END IF;
	END IF;
	
	IF	NEW.order_id IS NOT NULL
	THEN
		-- Fetch key order details
		SELECT	id,
				user_id,
				campaign_id,
				aff_id
		INTO	o
		FROM	orders
		WHERE	id = NEW.order_id;
	ELSEIF NEW.product_id IS NOT NULL
	THEN
		-- Auto-create order using the product_id
		INSERT INTO orders(
				status,
				user_id,
				campaign_id
				)
		SELECT	NEW.status,
				NEW.user_id,
				COALESCE(NEW.coupon_id, promo.id)
		FROM	products
		LEFT JOIN active_promos as promo
		ON		promo.product_id = products.id
		LEFT JOIN campaigns as campaign -- Non-active coupons are sanitized later
		ON		campaign.id = NEW.coupon_id
		AND		promo.product_id = products.id
		WHERE	products.id = NEW.product_id
		RETURNING id,
				user_id,
				campaign_id,
				aff_id
		INTO	o;
		NEW.order_id := o.id;
	ELSEIF NEW.coupon_id IS NOT NULL
	THEN
		-- Auto-create order using the coupon_id
		INSERT INTO orders(
				status,
				user_id,
				campaign_id,
				aff_id
				)
		SELECT	NEW.status,
				NEW.user_id,
				NEW.coupon_id,
				campaign.aff_id
		FROM	campaigns as campaign -- Non-active coupons are sanitized later
		WHERE	campaign.id = NEW.coupon_id
		RETURNING id,
				user_id,
				campaign_id,
				aff_id
		INTO	o;
		NEW.order_id := o.id;
	ELSE
		-- Auto-create order
		INSERT INTO orders (
				status,
				user_id
				)
		VALUES (
				NEW.status,
				NEW.user_id
				)
		RETURNING id,
				user_id,
				campaign_id,
				aff_id
		INTO	o;
		NEW.order_id := o.id;
	END IF;
	
	IF	NEW.product_id IS NULL
	THEN
		-- Bail
		IF	o.aff_id IS NULL
		THEN
			NEW.init_comm := 0;
			NEW.rec_comm := 0;		
		END IF;
		
		NEW.coupon_id := NULL;
		RETURN NEW;
	END IF;
	
	-- Validate product
	SELECT	init_price,
			init_comm,
			rec_price,
			rec_comm,
			rec_interval,
			rec_count
	INTO	p
	FROM	products
	WHERE	id = NEW.product_id
	AND		status > 'draft';
	
	IF	NOT FOUND
	THEN
		RAISE EXCEPTION 'Cannot tie inactive products.id = % to order_lines.id = %.',
			NEW.product_id, NEW.id;
	END IF;
	
	IF	ROW(NEW.init_discount, NEW.rec_discount) = ROW(0, 0)
	THEN
		-- Squash the coupon
		NEW.coupon_id := NULL;
	END IF;
	
	IF	NEW.coupon_id IS NOT NULL OR NEW.init_discount IS NULL OR NEW.rec_discount IS NULL
	THEN
		IF	NEW.coupon_id IS NOT NULL
		THEN
			-- Validate coupon
			SELECT	aff_id,
					init_discount,
					rec_discount,
					firesale,
					release,
					expire,
					stock
			INTO	c
			FROM	active_coupons
			WHERE	id = NEW.coupon_id
			AND		product_id = NEW.product_id;
		
			IF	NOT FOUND OR
				-- Drop inconsistent sponsors
				c.aff_id IS NOT NULL AND o.campaign_id IS NOT NULL AND c.aff_id IS DISTINCT FROM o.aff_id
			THEN
				NEW.coupon_id := NULL;
			END IF;
		END IF;
	
		IF	NEW.coupon_id IS NULL AND o.campaign_id IS NOT NULL
		THEN
			-- Autofech coupon
			SELECT	aff_id,
					init_discount,
					rec_discount,
					firesale,
					release,
					expire,
					stock
			INTO	c
			FROM	active_coupons
			WHERE	id = o.campaign_id
			AND		product_id = NEW.product_id;
		
			IF	FOUND
			THEN
				NEW.coupon_id := o.campaign_id;
			END IF;
		END IF;
	
		IF	NEW.coupon_id IS NULL
		THEN
			-- Autofetch promo
			SELECT	aff_id,
					init_discount,
					rec_discount,
					firesale,
					release,
					expire,
					stock
			INTO	c
			FROM	active_promos
			WHERE	promo_id = NEW.product_id;
		
			IF	FOUND
			THEN
				NEW.coupon_id := o.campaign_id;
			END IF;
		END IF;
	END IF;
	
	IF	NEW.coupon_id IS NULL
	THEN
		-- Sanitize amounts
		NEW.init_discount := COALESCE(NEW.init_discount, 0);
		NEW.rec_discount := COALESCE(NEW.rec_discount, 0);
		NEW.init_amount := COALESCE(NEW.init_amount, p.init_price - NEW.init_discount);
		NEW.rec_amount := COALESCE(NEW.rec_amount, p.rec_price - NEW.rec_discount);
		IF	o.aff_id IS NULL
		THEN
			NEW.init_comm := 0;
			NEW.rec_comm := 0;
		ELSE -- Assume a site discount, if any
			NEW.init_comm := LEAST(COALESCE(NEW.init_comm, p.init_comm), NEW.init_amount);
			NEW.rec_comm := LEAST(COALESCE(NEW.rec_comm, p.rec_comm), NEW.rec_amount);
		END IF;
	ELSE
		-- Process firesale if any
		IF	c.firesale
		THEN
			IF	c.expire IS NOT NULL
			THEN
				t_ratio := EXTRACT(EPOCH FROM c.expire - NOW()::datetime) /
					EXTRACT(EPOCH FROM c.expire - c.release);
			END IF;
		
			IF	c.stock IS NOT NULL
			THEN
				SELECT	SUM(order_lines.quantity)
				INTO	cur_orders
				FROM	order_lines
				JOIN	orders
				ON		orders.id = order_lines.order_id
				WHERE	order_lines.order_id <> NEW.order_id
				AND		order_lines.coupon_id = NEW.coupon_id
				AND		order_lines.status > 'pending'
				AND		orders.cleared >= c.release;
		
				o_ratio := c.stock / ( COALESCE(cur_orders, 0) + c.stock );
			END IF;
		
			c.init_discount := round(c.init_discount * t_ratio * o_ratio, 2);
			c.rec_discount := round(c.rec_discount * t_ratio * o_ratio, 2);
		END IF;
		
		-- Sanitize amounts
		NEW.init_discount := COALESCE(NEW.init_discount, c.init_discount, 0);
		NEW.rec_discount := COALESCE(NEW.rec_discount, c.rec_discount, 0);
		NEW.init_amount := COALESCE(NEW.init_amount, p.init_price - NEW.init_discount);
		NEW.rec_amount := COALESCE(NEW.rec_amount, p.rec_price - NEW.rec_discount);
		IF	c.aff_id IS NULL
		THEN
			NEW.init_comm := LEAST(COALESCE(NEW.init_comm, p.init_comm), NEW.init_amount);
			NEW.rec_comm := LEAST(COALESCE(NEW.rec_comm, p.rec_comm), NEW.rec_amount);
		ELSE
			NEW.init_comm := LEAST(COALESCE(NEW.init_comm, p.init_comm - NEW.init_discount), NEW.init_amount);
			NEW.rec_comm := LEAST(COALESCE(NEW.rec_comm, p.rec_comm - NEW.rec_discount), NEW.rec_amount);
		END IF;
	END IF;
	
	-- Fetch interval/count
	IF	NEW.rec_amount > 0
	THEN
		NEW.rec_interval := COALESCE(NEW.rec_interval, p.rec_interval);
		NEW.rec_count := COALESCE(NEW.rec_count, p.rec_count);
	ELSE
		NEW.rec_interval := NULL;
		NEW.rec_count := NULL;
	END IF;
	
	--RAISE NOTICE '%, %, % / %, %, %',
	--	NEW.init_amount, NEW.init_amount, NEW.init_discount,
	--	NEW.rec_amount, NEW.rec_amount, NEW.rec_discount;
	
	RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER order_lines_02_sanitize_amounts
	BEFORE INSERT OR UPDATE ON order_lines
FOR EACH ROW EXECUTE PROCEDURE order_lines_sanitize_amounts();

/**
 * Sanitizes an order_line's shipping user.
 */
CREATE OR REPLACE FUNCTION order_lines_sanitize_user_id()
	RETURNS trigger
AS $$
DECLARE
	u_id		bigint;
BEGIN
	IF	NEW.user_id IS NULL
	THEN
		RETURN NEW;
	ELSEIF TG_OP = 'UPDATE'
	THEN
		IF	NEW.user_id IS NOT DISTINCT FROM OLD.user_id
		THEN
			RETURN NEW;
		END IF;
	END IF;
	
	IF	NOT EXISTS (
		SELECT	1
		FROM	users
		WHERE	id = NEW.user_id
		AND		status > 'pending'
		)
	THEN
		RAISE EXCEPTION 'Cannot tie inactive users.id = % to order_lines.id = %.',
			NEW.user_id, NEW.id;
	END IF;
	
	RETURN NEW;
END $$ LANGUAGE plpgsql;

CREATE TRIGGER order_lines_03_sanitize_user_id
	BEFORE INSERT OR UPDATE ON order_lines
FOR EACH ROW EXECUTE PROCEDURE order_lines_sanitize_user_id();

/**
 * Forward status changes to orders
 */
CREATE OR REPLACE FUNCTION order_lines_delegate_status()
	RETURNS trigger
AS $$
DECLARE
	new_status	status_payable;
BEGIN
	IF	TG_OP = 'UPDATE'
	THEN
		IF	ROW(NEW.status, NEW.order_id) = ROW(OLD.status, OLD.order_id)
		THEN
			RETURN NEW;
		ELSEIF NEW.order_id <> OLD.order_id
		THEN
			-- Also do this for the old order
			SELECT	MAX(order_lines.status)
			INTO	new_status
			FROM	order_lines
			WHERE	order_id = OLD.order_id;
			
			new_status := COALESCE(new_status, 'trash');
			
			UPDATE	orders
			SET		status = new_status
			WHERE	orders.id = OLD.order_id
			AND		status <> new_status;
			
			-- RAISE NOTICE '%, %', TG_NAME, FOUND;
		END IF;
	END IF;
	
	SELECT	MAX(order_lines.status)
	INTO	new_status
	FROM	order_lines
	WHERE	order_id = NEW.order_id;
	
	UPDATE	orders
	SET		status = new_status
	WHERE	orders.id = NEW.order_id
	AND		status <> new_status;
	
	-- RAISE NOTICE '%, %', TG_NAME, FOUND;
	
	RETURN NEW;
END $$ LANGUAGE plpgsql;

CREATE TRIGGER order_lines_10_delegate_status
	AFTER INSERT OR UPDATE ON order_lines
FOR EACH ROW EXECUTE PROCEDURE order_lines_delegate_status();

/**
 * Forwards coupon_id to orders.
 *
 * Doing the reciprocal query, or checking the validity of coupon owners
 * on order updates, is invalid because campaign owners may change.
 */
CREATE OR REPLACE FUNCTION order_lines_delegate_campaign_id()
	RETURNS trigger
AS $$
BEGIN
	IF	NEW.coupon_id IS NULL
	THEN
		RETURN NEW;
	ELSEIF TG_OP = 'UPDATE'
	THEN
		IF	NEW.coupon_id IS NOT DISTINCT FROM OLD.coupon_id
		THEN
			RETURN NEW;
		END IF;
	END IF;
	
	UPDATE	orders
	SET		campaign_id = NEW.coupon_id,
			aff_id = campaigns.aff_id
	FROM	campaigns
	WHERE	orders.id = NEW.order_id
	AND		campaign_id IS NULL
	AND		campaigns.id = NEW.coupon_id;
	
	-- RAISE NOTICE '%, %', TG_NAME, FOUND;
	
	RETURN NEW;
END $$ LANGUAGE plpgsql;

CREATE TRIGGER order_lines_15_delegate_campaign_id
	AFTER INSERT OR UPDATE ON order_lines
FOR EACH ROW EXECUTE PROCEDURE order_lines_delegate_campaign_id();