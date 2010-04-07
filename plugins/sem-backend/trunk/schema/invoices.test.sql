\echo
\echo '#'
\echo '# Testing invoices'
\echo '#'
\echo

INSERT INTO invoices DEFAULT VALUES;
INSERT INTO invoice_lines DEFAULT VALUES;

DELETE FROM invoices;

INSERT INTO users ( name, email )
VALUES	( 'Joe', 'joe@bar.com' ),
		( 'Jack', 'jack@bar.com' );

INSERT INTO products ( init_price, init_comm ) VALUES ( 12, 6 );

INSERT INTO orders ( user_id, aff_id )
SELECT	u.id,
		a.id
FROM	get_user('joe@bar.com') as u,
		get_user('jack@bar.com') as a;

INSERT INTO order_lines ( order_id, user_id, product_id )
SELECT	orders.id,
		users.id,
		products.id
FROM	orders,
		get_user('joe@bar.com') as users,
		products;

INSERT INTO invoice_lines ( order_line_id )
SELECT	id
FROM	order_lines;

UPDATE	invoice_lines
SET		cleared_amount = due_amount,
		status = 'cleared';

SELECT	'Delegate invoice_line status',
		status = 'cleared'
FROM	invoice_lines
WHERE	payment_type = 'payment';

SELECT	'Handle commissions for cleared invoices',
		status = 'pending'
FROM	invoice_lines
WHERE	payment_type = 'commission';

UPDATE	invoice_lines
SET		cleared_amount = 0,
		status = 'reversed'
WHERE	payment_type = 'payment';

SELECT	'Handle commissions for reversed invoices',
		status = 'cancelled'
FROM	invoice_lines
WHERE	payment_type = 'commission';

-- clean up
/*
--\! sleep 3

\echo '# Cleaning up...'
\echo

-- DELETE FROM transaction_lines;
-- DELETE FROM transactions;
DELETE FROM invoice_lines;
DELETE FROM invoices;
DELETE FROM order_lines;
DELETE FROM orders;
DELETE FROM products;
DELETE FROM campaigns;
DELETE FROM users;
--*/