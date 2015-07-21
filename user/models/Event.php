<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <https://github.com/d4w33d/CalBox>.
 */

// =============================================================================

namespace CalBox\Models;

// -----------------------------------------------------------------------------

use CalBox\Lib\Model;
use CalBox\Lib\Lang;
use CalBox;
use ORM;

// =============================================================================

class Event extends Model
{

    // =========================================================================

    public static function findAllBetweenDates($begin, $end)
    {
        if (is_numeric($begin)) $begin = date('Y-m-d H:i:s', $begin);
        if (is_numeric($end)) $end = date('Y-m-d H:i:s', $end);

        $where = '';
        $params = [];

        $where .= '(`begins_at` BETWEEN ? AND ?)';
        $params = array_merge($params, [$begin, $end]);

        $where .= ' OR (`ends_at` BETWEEN ? AND ?)';
        $params = array_merge($params, [$begin, $end]);

        $where .= ' OR (`begins_at` < ? AND `ends_at` > ?)';
        $params = array_merge($params, [$begin, $end]);

        $query = ORM::for_table((new self())->getTableName())
            ->where_raw($where, $params)
            ->order_by_asc('begins_at')
            ->order_by_asc('ends_at')
            ->order_by_asc('title');

        return self::factory($query->find_many());
    }

    // =========================================================================

    public $id;
    public $created_at;
    public $updated_at;
    public $begins_at;
    public $ends_at;
    public $title;
    public $info;
    public $options;

    // -------------------------------------------------------------------------

    /**
     * User's constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns the "id" field of the table
     * @return string
     */
    public function getIdentifyerField()
    {
        return 'id';
    }

    // -------------------------------------------------------------------------

    public function beforeCreate()
    {
        $this->updated_at = date('Y-m-d H:i:s');
        $this->created_at = $this->updated_at;
    }

    public function beforeUpdate()
    {
        $this->updated_at = date('Y-m-d H:i:s');
    }

    // -------------------------------------------------------------------------

    public function getFormattedBeginsAt($withNames = false)
    {
        return $this->getFormattedDate($this->begins_at, $withNames);
    }

    public function getFormattedEndsAt($withNames = false)
    {
        return $this->getFormattedDate($this->ends_at, $withNames);
    }

    public function getFormattedDate($date, $withNames = false)
    {
        $ts = $date;
        if (!is_numeric($ts)) {
            $ts = strtotime($ts);
        }

        if (!$withNames) {
            return date('d/m/Y H:i', $ts);
        }

        return $date;
    }

    public function getBaseOptions()
    {
        return array_map(function($opt) {
            return array_merge([
              'label' => '',
              'type' => 'string',
              'nullable' => true,
              'default' => null,
              'choices' => [],
              'placeholder' => null,
            ], $opt);
        }, CalBox::cfg('behaviour.options'));
    }

    public function getRegistrations()
    {
        return Registration::findByEvent($this->id);
    }

    public function getDaysRange()
    {
        $days = [];
        $begin = strtotime(date('Y-m-d 00:00:00', strtotime($this->begins_at)));
        $end = strtotime(date('Y-m-d 00:00:00', strtotime($this->ends_at)));

        for ($s = $begin; $s <= $end; $s += 24 * 3600) {
            $days[] = $s;
        }

        return $days;
    }

}
