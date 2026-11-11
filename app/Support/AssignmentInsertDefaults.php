<?php

declare(strict_types=1);

namespace App\Support;

/**
 * service_assignments.status and technician_response are NOT NULL in the
 * migrated schema, but the architecture document does not define allowed
 * strings or an accept/reject/start workflow. These values are the minimum
 * needed to insert an assignment row. Do not treat them as a state machine.
 */
final class AssignmentInsertDefaults
{
    public const STATUS = 'assigned';

    public const TECHNICIAN_RESPONSE = 'unspecified';
}
