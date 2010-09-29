<?php
namespace Sonic\Database\Query;
use Sonic\Database\Query;
use ArrayIterator;

/**
 * Query Filter Class
 *
 * @category Sonic
 * @package Database
 * @subpackage Query
 * @author Craig Campbell
 */
class Filter
{
    /**
     * @var array
     */
    protected $_patterns = array();

    /**
     * @var int
     */
    protected $_total_weight = 0;

    /**
     * adds a pattern to filter on
     *
     * @param string
     * @return void
     */
    public function addPattern($pattern, $args = null)
    {
        $this->_patterns[] = $this->_processPattern($pattern, $args);
    }

    /**
     * takes pattern like "id<5" and converts it to
     * array('id', '<', '5')
     *
     * @param string
     * @return array
     */
    protected function _processPattern($pattern, $args)
    {
        // the order here matters cause if = came before == then it would match that even if the user used ==
        $allowed_symbols = array('<=', '>=', '<>', '!=', '===', '==', '<', '>', '=', 'NOT IN', 'LIKE', 'IN', 'FULLTEXT');

        $valid = false;
        foreach ($allowed_symbols as $symbol) {
            if (strpos($pattern, $symbol)) {
                $valid = true;
                break;
            }
        }

        // if the filter is invalid
        if (!$valid) {
            throw new \Sonic\Database\Exception('symbol used for filter must be one of: ' .
                implode(', ', $allowed_symbols));
        }

        if ($symbol == 'FULLTEXT') {
            $args = $args !== null ? $args : 1;
            $this->_total_weight += $args;
        }

        $bits = explode($symbol, $pattern);

        $filter = array(
            'column' => trim($bits[0]),
            'comparison' => $symbol,
            'value' => trim($bits[1]),
            'args' => $args
        );

        return $filter;
    }

    /**
     * takes unfiltered data and processes filters
     *
     * @uses Filter\Iterator
     * @param array $rows
     * @return array
     */
    public function process(array $rows)
    {
        $unfiltered = new Filter\Iterator();
        $unfiltered->setPatterns($this->_patterns);

        $filtered = $unfiltered->process($rows, $this->_total_weight);

        return $filtered;
    }
}
