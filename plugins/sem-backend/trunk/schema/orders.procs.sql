/**
 * Checks integrity when an order is trashed.
 */
CREATE OR REPLACE FUNCTION orders_check_trash()
	RETURNS trigger
AS $$
BEGIN
	IF NEW.status = OLD.status OR NEW.status <> 'trash'
	THEN
		RETURN NEW;
	END IF;
	
	IF	EXISTS (
		SELECT	1
		FROM	order_lines
		WHERE	order_id = NEW.id -- cascade updated
		)
	THEN
		RAISE EXCEPTION 'Cannot delete orders.id = %: it is referenced in order_lines.order_id.', NEW.id;
	END IF;
	
	RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER orders_01_check_trash
	AFTER UPDATE ON orders
FOR EACH ROW EXECUTE PROCEDURE orders_check_trash();

/**
 * Sanitizes an orders's campaign.
 */
CREATE OR REPLACE FUNCTION orders_sanitize_campaign_id()
	RETURNS trigger
AS $$
DECLARE
	a_id		bigint;
BEGIN
	IF	TG_TABLE_NAME <> 'orders' OR -- Trust triggers
		NEW.campaign_id IS NULL
	THEN
		RETURN NEW;
	ELSEIF TG_OP = 'UPDATE'
	THEN
		IF	NEW.campaign_id IS NOT DISTINCT FROM OLD.campaign_id
		THEN
			RETURN NEW;
		END IF;
	END IF;
	
	SELECT	aff_id
	INTO	a_id
	FROM	active_campaigns
	WHERE	id = NEW.campaign_id;
	
	IF	NOT FOUND
	THEN
		RAISE EXCEPTION 'Cannot tie campaigns.id = % to orders.id = %: campaign isn''t active.',
			NEW.campaign_id, NEW.id;
	ELSEIF TG_OP = 'INSERT'
	THEN
		-- auto-correct aff_id on inserts
		NEW.aff_id := a_id;
	END IF;
	
	RETURN NEW;
END $$ LANGUAGE plpgsql;

CREATE TRIGGER campaigns_02_sanitize_campaign_id
	BEFORE INSERT OR UPDATE ON orders
FOR EACH ROW EXECUTE PROCEDURE orders_sanitize_campaign_id();

/**
 * Sanitizes an orders's billing user.
 */
CREATE OR REPLACE FUNCTION orders_sanitize_user_id()
	RETURNS trigger
AS $$
DECLARE
	u_id		bigint;
BEGIN
	IF	TG_TABLE_NAME <> 'orders' OR -- Trust triggers
		NEW.user_id IS NULL
	THEN
		RETURN NEW;
	ELSEIF TG_OP = 'UPDATE'
	THEN
		IF	NEW.user_id IS NOT DISTINCT FROM OLD.user_id
		THEN
			RETURN NEW;
		END IF;
	END IF;
	
	IF	NOT EXISTS(
		SELECT	1
		FROM	active_users
		WHERE	id = NEW.user_id
		)
	THEN
		RAISE EXCEPTION 'Cannot tie users.id = % to orders.id = %: user isn''t active.',
			NEW.user_id, NEW.id;
	END IF;
	
	RETURN NEW;
END $$ LANGUAGE plpgsql;

CREATE TRIGGER campaigns_02_sanitize_user_id
	BEFORE INSERT OR UPDATE ON orders
FOR EACH ROW EXECUTE PROCEDURE orders_sanitize_user_id();

/**
 * Sanitizes an orders's billing user.
 */
CREATE OR REPLACE FUNCTION orders_sanitize_aff_id()
	RETURNS trigger
AS $$
DECLARE
	u_id		bigint;
BEGIN
	IF	TG_TABLE_NAME <> 'orders' OR -- Trust triggers
		NEW.aff_id IS NULL
	THEN
		RETURN NEW;
	ELSEIF TG_OP = 'UPDATE'
	THEN
		IF	NEW.aff_id IS NOT DISTINCT FROM OLD.aff_id
		THEN
			RETURN NEW;
		END IF;
	END IF;
	
	IF	NOT EXISTS(
		SELECT	1
		FROM	active_users
		WHERE	id = NEW.aff_id
		)
	THEN
		RAISE EXCEPTION 'Cannot tie users.id = % to orders.id = %: user isn''t active.',
			NEW.aff_id, NEW.id;
	END IF;
	
	RETURN NEW;
END $$ LANGUAGE plpgsql;

CREATE TRIGGER campaigns_02_sanitize_aff_id
	BEFORE INSERT OR UPDATE ON orders
FOR EACH ROW EXECUTE PROCEDURE orders_sanitize_aff_id();