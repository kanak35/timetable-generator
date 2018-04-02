<?php

namespace App\Services;

use App\Models\CollegeClass;

class CollegeClassesService extends AbstractService
{
    /*
     * The model to be used by this service.
     *
     * @var \App\Models\CollegeClass
     */
    protected $model = CollegeClass::class;

    /**
     * Show resources with their relations.
     *
     * @var bool
     */
    protected $showWithRelations = true;

    /**
     * Add a new college class
     *
     * @param array $data Data for creating a new college class
     * @return App\Models\CollegeClass Newly created class
     */
    public function store($data = [])
    {
        $class = CollegeClass::create([
            'name' => $data['name'],
            'size' => $data['size']
        ]);

        if (!$class) {
            return null;
        }

        $class->unavailable_rooms()->sync($data['unavailable_rooms']);
        $class->courses()->sync($data['courses']);

        return $class;
    }

    /**
     * Update the class with the given id
     *
     * @param int $id The ID of the class
     * @param array $data Data
     */
    public function update($id, $data = [])
    {
        $class = CollegeClass::find($id);

        if (!$class) {
            return null;
        }

        $class->update([
            'name' => $data['name'],
            'size' => $data['size']
        ]);

        $class->unavailable_rooms()->sync($data['unavailable_rooms']);
        $class->courses()->sync($data['courses']);

        return $class;
    }
}