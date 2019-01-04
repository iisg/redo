-- fix not existing metadata breaking tests

UPDATE resource SET contents = contents - '171' WHERE id = 2382;
UPDATE resource SET contents = contents - '171' WHERE id = 2381;
INSERT INTO metadata VALUES (-6, 'relationship', '{"EN": "Visibility", "PL": "Widoczność"}', '[]', '[]', 'visibility', NULL, -1, NULL, '{}', false, '', false, 'DEFAULT_GROUP');
INSERT INTO metadata VALUES (-7, 'relationship', '{"EN": "Visibility in relationship", "PL": "Widoczność w relacjach"}', '[]', '[]', 'visibility_in_relationship', NULL, -1, NULL, '{}', false, '', false, 'DEFAULT_GROUP');
