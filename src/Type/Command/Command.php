<?php

declare(strict_types=1);

namespace HelloCoop\Type\Command;

enum Command: string
{
    case METADATA = 'metadata';
    case UNAUTHORIZE = 'unauthorize';
    case ACTIVATE = 'activate';
    case SUSPEND = 'suspend';
    case REACTIVATE = 'reactivate';
    case ARCHIVE = 'archive';
    case RESTORE = 'restore';
    case DELETE = 'delete';
    case AUDIT_TENANT = 'audit_tenant';
    case UNAUTHORIZE_TENANT = 'unauthorize_tenant';
    case SUSPEND_TENANT = 'suspend_tenant';
    case ARCHIVE_TENANT = 'archive_tenant';
    case DELETE_TENANT = 'delete_tenant';
}
