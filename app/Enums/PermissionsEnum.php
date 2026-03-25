<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    // Permissions on User Browsing
    case VIEW_MY_POSTS = 'view_my_posts';
    case CREATE_MY_POST = 'create_my_post';
    case VIEW_MY_COMMENTS = 'view_my_comments';
    case CREATE_MY_COMMENT = 'create_my_comment';
    case SELECT_REACT = 'select_react';
    case VIEW_BLOG_CONTENT = 'view_blog_content';
    // Permissions on Users
    case VIEW_USERS = 'view_users';
    case CHANGE_USER_ROLES = 'change_user_roles';
    case CHANGE_VIU_ROLES = 'change_viu_roles';
    case VI_User = 'vi_user';
    case BAN_USER = 'ban_user';
    case ACTIVATE_USER = 'activate_user';
    case DESTROY_USER = 'destroy_user';
    // Permissions on Roles
    case VIEW_ROLES = 'view_roles';
    case CREATE_ROLE = 'create_role';
    case UPDATE_ROLE = 'update_role';
    case DELETE_ROLE = 'delete_role';

    public static function values(): array
    {
        return array_column(self::cases(),'value');
    }
}
