-- fix not existing metadata breaking tests

UPDATE resource SET contents = contents - '171' WHERE id = 2382;
UPDATE resource SET contents = contents - '171' WHERE id = 2381;