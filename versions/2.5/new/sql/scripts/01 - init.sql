drop table if exists `site_builder`;
create table `site_builder` (
	siteBuilderId int auto_increment primary key not null,
	name varchar(50) not null default '',
	extra varchar(50) not null default ''
);

drop table if exists `site_user`;
create table `site_user` (
	siteUserId int auto_increment primary key not null,
	username varchar(50) not null default '',
	password varchar(50) not null default ''
);

drop table if exists `site_user_info`;
create table `site_user_info` (
	siteUserInfoId int auto_increment primary key not null,
	siteUserId int not null default 0,
	info varchar(50) not null default ''
);
