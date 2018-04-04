<?php

namespace App\Services\GeneticAlgorithm;

use Storage;

use App\Models\Day as DayModel;
use App\Models\Room as RoomModel;
use App\Models\Course as CourseModel;
use App\Models\Timeslot as TimeslotModel;
use App\Models\CollegeClass as CollegeClassModel;
use App\Models\Professor as ProfessorModel;

class TimetableRenderer
{
    /**
     * Create a new instance of this class
     *
     * @param App\Models\Timetable Timetable whose data we are rendering
     */
    public function __construct($timetable)
    {
        $this->timetable = $timetable;
    }

    /**
     * Generate HTML layout files out of the timetable data
     *
     * Chromosome interpretation is as follows
     * Timeslot, Room, Professor
     *
     */
    public function render()
    {
        $chromosome = explode(",", $this->timetable->chromosome);
        $scheme = explode(",", $this->timetable->scheme);
        $data = $this->generateData($chromosome, $scheme);

        $days = $this->timetable->days()->orderBy('id', 'ASC')->get();
        $timeslots = TimeslotModel::orderBy('rank', 'ASC')->get();
        $classes = CollegeClassModel::all();

        $tableTemplate =  Storage::get('/storage/timetables/template.html');
        $htmlTemplate  = Storage::get('/storage/timetables/html_template.html');

        $content = "";

        foreach ($classes as $class) {
            $header = "<tr class='table-head'>";
            $header .= "<td>Days</td>";

            foreach ($timeslots as $timeslot) {
                $header .= "\t<td>" . $timeslot->time . "</td>";
            }

            $header .= "</tr>";

            $body = "";

            foreach ($days as $day) {
                $body .= "<tr><td>" . strtoupper($day->name) . "</td>";
                foreach ($timeslots as $timeslot) {
                    if (isset($data[$class->id][$day->name][$timeslot->time])) {
                        $body .= "<td>";
                        $slotData = $data[$class->id][$day->name][$timeslot->time];
                        $courseCode = $slotData['course_code'];
                        $courseName = $slotData['course_name'];
                        $professor = $slotData['professor'];
                        $room = $slotData['room'];

                        $body .= "<span class='course_code'>{$courseCode}</span><br />";
                        $body .= "<span class='room'>{$room}</span>";
                        $body .= "<span class='professor'>{$professor}</span>";

                        $body .= "</td>";
                    } else {
                        $body .= "<td></td>";
                    }
                }
                $body .= "</tr>";
            }

            $content .= str_replace(['{HEADING}', '{BODY}'], [$header, $body], $tableTemplate);
        }

        $finalHtml = str_replace(['{TITLE}', '{CONTENT}'], [$this->timetable->name, $content], $htmlTemplate);

        Storage::put('storage/timetables/timetable_' . $this->timetable->id . '.html', $finalHtml);
    }

    /**
     * Get an associative array with data for constructing timetable
     *
     * @param array $chromosome Timetable chromosome
     * @param array $scheme Mapping for reading chromosome
     * @return array Timetable data
     */
    public function generateData($chromosome, $scheme)
    {
        $data = [];
        $schemeIndex = 0;
        $chromosomeIndex = 0;
        $groupId = null;

        while ($chromosomeIndex < count($chromosome)) {
            if ($scheme[$schemeIndex][0] == 'G') {
                $groupId = substr($scheme[$schemeIndex], 1);
                $schemeIndex += 1;
            }

            $courseId = $scheme[$schemeIndex];

            $class = CollegeClassModel::find($groupId);
            $course = CourseModel::find($courseId);

            $timeslotGene = $chromosome[$chromosomeIndex];
            $roomGene = $chromosome[$chromosomeIndex + 1];
            $professorGene = $chromosome[$chromosomeIndex + 2];

            $matches = [];
            preg_match('/D(\d*)T(\d*)/', $timeslotGene, $matches);

            $dayId = $matches[1];
            $timeslotId = $matches[2];

            $day = DayModel::find($dayId);
            $timeslot = TimeslotModel::find($timeslotId);
            $professor = ProfessorModel::find($professorGene);
            $room = RoomModel::find($roomGene);

            if (!isset($data[$groupId])) {
                $data[$groupId] = [];
            }

            if (!isset($data[$groupId][$day->name])) {
                $data[$groupId][$day->name] = [];
            }

            if (!isset($data[$groupId][$day->name][$timeslot->time])) {
                $data[$groupId][$day->name][$timeslot->time] = [];
            }

            $data[$groupId][$day->name][$timeslot->time]['course_code'] = $course->course_code;
            $data[$groupId][$day->name][$timeslot->time]['course_name'] = $course->name;
            $data[$groupId][$day->name][$timeslot->time]['room'] = $room->name;
            $data[$groupId][$day->name][$timeslot->time]['professor'] = $professor->name;

            $schemeIndex++;
            $chromosomeIndex += 3;
        }

        return $data;
    }
}