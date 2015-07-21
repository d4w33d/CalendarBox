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

namespace CalBox\Lib;

// -----------------------------------------------------------------------------

use CalBox\Lib\Application;
use CalBox\Models\Event;
use CalBox\Models\Registration;
use CalBox;
use ORM;
use Exception;

// =============================================================================

class Controller
{

    // =========================================================================

    public function onIndex(Application $app)
    {
        if (!($m = $app->p('m')) || !preg_match('/^[0-9]{6}$/', $m)) {
            $m = date('Ym');
        }

        $year = substr($m, 0, 4);
        $month = substr($m, 4, 2);

        $currentM = date('Ym');

        // Begin and ending timestamp
        $ts = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth = date('t', $ts);
        $lastDayPos = date('N', mktime(0, 0, 0, $month, $daysInMonth, $year));

        $begin = $ts - (date('N', $ts) - 1) * 24 * 3600;
        $end = $ts
            + (($daysInMonth - 1) * 24 * 3600)
            + ((7 - $lastDayPos) * 24 * 3600);

        // All events for this month
        $events = Event::findAllBetweenDates($begin, $end);

        // Previous and next month
        $nav = (object) array_map(function($s) use ($currentM)
        {
            $m = date('Ym', $s);

            return (object) [
                's' => $s,
                'm' => $m,
                'link' => CalBox::makeUrl('index', [
                    'm' => $m === $currentM ? null : $m,
                ]),
            ];
        }, [
            'prev' => $begin - (24 * 3600),
            'next' => $end + (24 * 3600),
        ]);

        // Days iteration
        $days = [];
        for ($s = $begin; $s <= $end; $s += 24 * 3600) {
            $dayMonth = date('Ym', $s);

            $days[] = (object) [
                't' => $s,
                'n' => (int) date('j', $s),
                'day' => (int) date('N', $s),
                'is_current_month' => $dayMonth === $m,
                'is_prev_month' => $dayMonth === $nav->prev->m,
                'is_next_month' => $dayMonth === $nav->next->m,
                'events' => $this->getEventsForDay($events, $s),
            ];
        }

        // Rendering
        $app->render('index.phtml', [
            '__VIEW' => 'calendar',
            'days' => $days,
            'nav' => $nav,
            'ts' => $ts,
        ]);
    }

    private function getEventsForDay($events, $begin)
    {
        $dayEvents = [];

        $len = (24 * 3600) - 1;
        $end = $begin + $len;

        $colors = CalBox::cfg('behaviour.colors');
        $colorsNum = count($colors);

        foreach ($events as $event) {
            $eventBegin = strtotime($event->begins_at);
            $eventEnd = strtotime($event->ends_at);

            $l = 0;
            $r = 0;
            $is = false;

            if ($eventBegin >= $begin && $eventBegin <= $end) {
                $l = ($eventBegin - $begin) / $len;
                $is = true;
            }

            if ($eventEnd >= $begin && $eventEnd <= $end) {
                $r = ($end - $eventEnd) / $len;
                $is = true;
            }

            if (!$is && !($eventBegin < $begin && $eventEnd > $end)) {
                continue;
            }

            $w = 1 - ($l + $r);

            $color = $colors[$event->id % $colorsNum];

            $dayEvents[] = (object) [
                'left' => $l,
                'right' => $r,
                'width' => $w,
                'fg' => $color['fg'],
                'bg' => $color['bg'],
                'evt' => $event,
            ];
        }

        return $dayEvents;
    }

    public function onCreate(Application $app)
    {
        $this->onEdit($app);
    }

    public function onEdit(Application $app)
    {
        $event = new Event();
        if ($eventId = $app->p('e')) {
            if (!($event = Event::find($eventId))) {
                throw new Exception('Event #' . $eventId . ' not found');
            }
        }

        if (!CALBOX_IS_POST) {
            $app->render('edit.phtml', [
                '__VIEW' => $event->id ? 'edit' : 'create',
                'event' => $event->id ? $event : null,
            ]);
            return;
        }

        $from = '/^([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2}):([0-9]{2})$/';
        $to = '$3-$2-$1 $4:$5:00';

        $event->begins_at = preg_replace($from, $to, $app->p('begins_at'));
        $event->ends_at = preg_replace($from, $to, $app->p('ends_at'));
        $event->title = $app->p('title');
        $event->info = $app->p('info', '');
        $event->options = json_encode($app->p('options') ?: []);

        $event->save();

        if (CALBOX_IS_AJAX) {
            $app->success();
            return;
        }

        CalBox::redirect(CalBox::makeUrl('edit', ['e' => $event->id]));
    }

    public function onDelete(Application $app)
    {
    }

    public function onAddRegistration(Application $app)
    {
        $this->onEditRegistration($app);
    }

    public function onEditRegistration(Application $app)
    {
        if (!($event = Event::find($app->p('e')))) {
            throw new Exception('Event #' . $app->p('e') . ' not found');
        }

        $registration = new Registration();
        if ($registrationId = $app->p('r')) {
            if (!($registration = Registration::find($registrationId))) {
                throw new Exception('Registration #'
                    . $registrationId . ' not found');
            }
            if ($registration->id_event !== $event->id) {
                throw new Exception('Registration #'
                    . $registrationId . ' is not associated to event #'
                    . $event->id);
            }
        }

        $options = [];
        $givenOptions = (array) $app->p('options');
        foreach ($event->getBaseOptions() as $optName => $opt) {
            $options[$optName] = array_key_exists($optName, $givenOptions) ?
                $givenOptions[$optName] : $opt['default'];
        }

        $registration->id_event = $event->id;
        $registration->name = $app->p('name');
        $registration->options = json_encode($options);

        $registration->save();

        if (CALBOX_IS_AJAX) {
            $app->success();
            return;
        }

        CalBox::redirect(CalBox::makeUrl('edit', ['e' => $event->id]));
    }

    public function onRemoveRegistration(Application $app)
    {
        if (!($event = Event::find($app->p('e')))) {
            throw new Exception('Event #' . $app->p('e') . ' not found');
        }

        if (!($registration = Registration::find($app->p('r')))) {
            throw new Exception('Registration #' . $app->p('r') . ' not found');
        }

        if ($registration->id_event !== $event->id) {
            throw new Exception('Registration #'
                . $registration->id . ' is not associated to event #'
                . $event->id);
        }

        $registration->delete();

        CalBox::redirect(CalBox::makeUrl('edit', ['e' => $event->id]));
    }

    public function onDb(Application $app)
    {
        if (!CalBox::cfg('debug')) {
            CalBox::redirect(CalBox::makeUrl('index'));
            return;
        }

        header('Content-type: text/plain; charset=utf-8');

        switch ($action = $app->p('action')) {
            case 'install':
                CalBox::executeDatabaseAction('install');
                die('INSTALLED.');
            case 'uninstall':
                CalBox::executeDatabaseAction('uninstall');
                die('UNINSTALLED.');
            case 'reinstall':
                CalBox::executeDatabaseAction('uninstall');
                CalBox::executeDatabaseAction('install');
                die('REINSTALLED.');
            default:
                die("Usage:\n"
                    . "  ./?db={action}\n"
                    . "  where {action} is one of the following:\n"
                    . "    - install\n"
                    . "    - uninstall\n"
                    . "    - reinstall\n");
        }
    }

}
