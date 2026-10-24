<?php

namespace App\Exceptions;

use LogicException;

/**
 * Thrown when any code tries to mutate an analysis_inputs row after
 * creation. Model events (updating/deleting) only catch Eloquent-level
 * changes on a loaded model instance — they do NOT catch query-builder
 * writes (AnalysisInput::query()->update(...)/delete()) or mass updates.
 * Nothing in this codebase may use those forms against this table.
 */
class AnalysisInputIsImmutableException extends LogicException
{
    protected $message = 'Analysis inputs are immutable and cannot be changed after creation.';
}
