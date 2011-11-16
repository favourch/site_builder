drop table if exists `site_builder`;
create table `site_builder` (
	id int auto_increment primary key not null,
	name varchar(50) not null default '',
	extra varchar(50) not null default ''
);
