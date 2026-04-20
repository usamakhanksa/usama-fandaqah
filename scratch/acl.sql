SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS permission_role;
DROP TABLE IF EXISTS role_user;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;

CREATE TABLE roles (
    id bigint unsigned auto_increment primary key,
    name varchar(255),
    slug varchar(255) unique,
    created_at timestamp null,
    updated_at timestamp null
) ENGINE=InnoDB;

CREATE TABLE permissions (
    id bigint unsigned auto_increment primary key,
    name varchar(255),
    slug varchar(255) unique,
    `group` varchar(255),
    created_at timestamp null,
    updated_at timestamp null
) ENGINE=InnoDB;

CREATE TABLE role_user (
    role_id bigint unsigned,
    user_id bigint unsigned,
    primary key(role_id, user_id)
) ENGINE=InnoDB;

CREATE TABLE permission_role (
    permission_id bigint unsigned,
    role_id bigint unsigned,
    enabled boolean default 0,
    anyone boolean default 0,
    can_create boolean default 0,
    can_edit boolean default 0,
    can_view boolean default 0,
    can_remove boolean default 0,
    created_at timestamp null,
    updated_at timestamp null,
    primary key(permission_id, role_id)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS=1;
