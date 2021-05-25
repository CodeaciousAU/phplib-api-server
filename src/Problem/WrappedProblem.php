<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\ApiServer\Problem;

use Laminas\ApiTools\ApiProblem\ApiProblem;

/**
 * Wraps an existing problem and masks any exception details contained within it, so that only a
 * generic error message will be returned to the end user (unless detailIncludesStackTrace is set).
 */
class WrappedProblem extends ApiProblem
{
    public function __construct(ApiProblem $problem)
    {
        parent::__construct($problem->status, $problem->detail);
        $this->status = $problem->status;
        $this->detail = $problem->detail;
        $this->type = $problem->type;
        $this->title = $problem->title;
        $this->additionalDetails = $problem->additionalDetails;
    }

    protected function createDetailFromException()
    {
        if (!$this->detailIncludesStackTrace)
            return 'An internal error occurred';

        return parent::createDetailFromException();
    }
}