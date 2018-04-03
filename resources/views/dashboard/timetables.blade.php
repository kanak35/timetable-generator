<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 colxs-12">
        @if (count($timetables))
        <table class="table table-bordered">
            <thead>
                <tr class="table-head">
                    <td>Timetable Name</td>
                    <td>Status</td>
                    <td>View</td>
                </tr>
            </thead>

            <tbody>
                @foreach ($timetables as $timetable)
                <tr>
                    <td>{{ $timetable->name }}</td>
                    <td>{{ $timetable->status }}</td>
                    <td>{{ $timetable->file_url ? $timetable->file_url : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
         <div id="pagination">
            {!!
                $timetables->render()
            !!}
        </div>
        @else
        <div class="no-data text-center">
            <p>No timetables generated yet</p>
        </div>
        @endif
    </div>
</div>