/*创建用户及对应数据库，用户对对应的数据库享有所有权限*/
CREATE USER 'loveServer'@'localhost' IDENTIFIED BY "loveServer";
GRANT USAGE ON * . * TO 'loveServer'@'localhost' IDENTIFIED BY "loveServer" WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;
CREATE DATABASE IF NOT EXISTS `loveServer` ;
GRANT ALL PRIVILEGES ON `loveServer` . * TO 'loveServer'@'localhost';

/*使用数据库*/
use loveServer;

/*创建表*/
create table user(
	/*账户本身信息*/
	uid bigint NOT NULL AUTO_INCREMENT,
	name varchar(100) NOT NULL,/*TODO：主键或者唯一*/
	password varchar(100) NOT NULL,
	nickName varchar(100) NOT NULL,
	score int NOT NULL,
	pairID bigint NOT NULL,
	
	moodValue varchar(100),/*心情*/
	primary key(uid)
) CHARACTER SET utf8 COLLATE utf8_general_ci;
insert user values(NULL,"wbx","wbx","co8bit",0,1,"未设置");
insert user values(NULL,"lxz","lxz","zhuzhu",0,1,"未设置");

create table bill(
	id bigint NOT NULL AUTO_INCREMENT,
	fromID bigint NOT NULL,
	toID bigint NOT NULL,
	isAdd bool NOT NULL,
	title TEXT NOT NULL,
	msgLast TEXT NOT NULL,
	msgPre TEXT NOT NULL,
	scoreLast int NOT NULL,
	scorePre int NOT NULL,
	timeLast datetime NOT NULL,
	timePre datetime NOT NULL,
	isEditFromID bool NOT NULL,
	isEditToID bool NOT NULL,
	lastIsFrom bool NOT NULL,
	isOver int NOT NULL,/*0:未完成；1：完成；3：完成但已经删掉*/
	primary key(id)
) CHARACTER SET utf8 COLLATE utf8_general_ci;


create table pair(
	id bigint NOT NULL AUTO_INCREMENT,
	user1ID bigint NOT NULL,
	user2ID bigint NOT NULL,
	pairDate datetime,
	primary key(id)
) CHARACTER SET utf8 COLLATE utf8_general_ci;
insert pair values(NULL,1,2,"2014-09-08 04:00:00");

create table rule(
	id bigint NOT NULL AUTO_INCREMENT,
	fromID bigint NOT NULL,
	idPair bigint NOT NULL,
	title TEXT NOT NULL,
	content TEXT NOT NULL,
	scoreAdd int NOT NULL,
	scoreSub int NOT NULL,
	isEditFromID bool NOT NULL,
	isEditToID bool NOT NULL,
	isOver bool NOT NULL,
	createTime datetime NOT NULL,
	primary key(id)
) CHARACTER SET utf8 COLLATE utf8_general_ci;