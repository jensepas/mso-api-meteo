<?php

declare(strict_types=1);

namespace Elasticsearch\Endpoints;

/**
 * Class Reindex
 *
 * @category Elasticsearch
 * @package  Elasticsearch\Endpoints\Indices
 * @author   Augustin Husson <husson.augustin@gmail.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache2
 * @link     http://elastic.co
 */
class Reindex extends AbstractEndpoint
{

    /**
     * @return string[]
     */
    public function getParamWhitelist()
    {
        return array(
            'slices',
            'refresh',
            'timeout',
            'consistency',
            'wait_for_completion',
            'requests_per_second',
        );
    }

    /**
     * @return string
     */
    public function getURI()
    {
        return '/_reindex';
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return 'POST';
    }

    /**
     * @param array $body
     *
     * @return $this
     * @throws \Elasticsearch\Common\Exceptions\InvalidArgumentException
     */
    public function setBody($body)
    {
        if (isset($body) !== true) {
            return $this;
        }

        $this->body = $body;

        return $this;
    }
}
