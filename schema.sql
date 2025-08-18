/*
* BookClinica Database
* @author HedaAllows
*/
drop database BookClinica;
create database BookClinica;
use BookClinica; 

create table user (
	id int not null auto_increment primary key,
	username varchar(50),
	name varchar(50),
	lastname varchar(50),
	email varchar(255),
	password varchar(60),
	is_active boolean not null default 1,
	is_admin boolean not null default 0,
	view_reports boolean not null default 1,
	view_users boolean not null default 1,
	edit_users boolean not null default 1,
	view_pacients boolean not null default 1,
	edit_pacients boolean not null default 1,
	view_medics boolean not null default 1,
	edit_medics boolean not null default 1,
	view_reservations boolean not null default 1,
	edit_reservations boolean not null default 1,
	created_at datetime
);

insert into user (name,username,password,is_admin,is_active,created_at) value ("Administrador","admin",sha1(md5("admin")),1,1,NOW());


create table pacient (
	id int not null auto_increment primary key,
	no varchar(50),
	image varchar(50),
	name varchar(50),
	lastname varchar(50),
	gender varchar(1),
	day_of_birth date,
	email varchar(255),
	address varchar(255),
	phone varchar(255),
	cp varchar(255),
	pob varchar(255),
	sick varchar(500),
	medicaments varchar(500),
	password varchar(60),
	alergy varchar(500),
	is_favorite boolean not null default 0,
	is_active boolean not null default 1,
	created_at datetime not null
);

create table category (
	id int not null auto_increment primary key,
	name varchar(200)
	);

insert into category (name) value ("Modulo 1");


create table medic (
	id int not null auto_increment primary key,
	image varchar(50),
	no varchar(50),
	name varchar(50),
	lastname varchar(50),
	gender varchar(1),
	day_of_birth date,
	email varchar(255),
	address varchar(255),
	phone varchar(255),
	is_active boolean not null default 1,
	password varchar(60),
	created_at datetime,
	category_id int,
	time1_data text,
	time2_data text,
	time3_data text,
	time4_data text,
	time5_data text,
	time6_data text,
	time7_data text,
	duration int,
	foreign key (category_id) references category(id)
);

create table medic_category(
	medic_id int not null,
	category_id int not null,
	foreign key(medic_id) references medic(id),
	foreign key(category_id) references category(id)
);


create table status (
	id int not null auto_increment primary key,
	name varchar(100)
);

insert into status (id,name) values (1,"Pendiente"), (2,"Aplicada"),(3,"No asistio"),(4,"Cancelada");

create table payment (
	id int not null auto_increment primary key,
	name varchar(100)
);

insert into payment (id,name) values  (1,"Pendiente"),(2,"Pagado"),(3,"Anulado");

create table reservation(
	id int not null auto_increment primary key,
	no varchar(100),
	title varchar(100),
	note text,
	message text,
	date_at date,
	time_at time,
	created_at datetime,
	pacient_id int,
	symtoms text,
	sick text,
	medicaments text,
	user_id int,
	medic_id int,
	duration int,/* duration in minutes */
	price double,
	is_web boolean not null default 0,
	payment_id int not null default 1,
	foreign key (payment_id) references payment(id),
	status_id int not null default 1,
	foreign key (status_id) references status(id),
	foreign key (user_id) references user(id),
	foreign key (pacient_id) references pacient(id),
	foreign key (medic_id) references medic(id)
);

/* for BookClinica */

create table treatment (
	id int not null auto_increment primary key,
	title varchar(255),
	description text,
	quantity varchar(100),
	start_at date,
	finish_at date,
	medic_id int,
	pacient_id int,
	create_at datetime,
	foreign key (pacient_id) references pacient(id),
	foreign key (medic_id) references medic(id)
);

create table doctype (
	id int not null auto_increment primary key,
	name varchar(255)
);

insert into doctype (name) values ("Documento"),("Imagen"),("Rayos X"),("Ultra sonido"),("Reporte de laboratorio");


create table file (
	id int not null auto_increment primary key,
	title varchar(255),
	description text,
	name varchar(100),
	start_at date,
	finish_at date,
	pacient_id int,
	doctype_id int,
	created_at datetime,
	foreign key (doctype_id) references doctype(id),
	foreign key (pacient_id) references pacient(id)
);
