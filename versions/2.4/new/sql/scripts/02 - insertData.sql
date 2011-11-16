insert into `site_builder`(name) values(concat('Test script line 1. ', now()));
insert into `site_builder`(name, extra) values(concat('Test script line 2. ', now()), 'Extra data');
