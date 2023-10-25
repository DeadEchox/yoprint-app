@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">CSV File Upload</div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('uploads.uploadCsv') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-8">
                                <label for="csv_file">Choose a CSV file:</label>
                                <input type="file" name="csv_file" id="csv_file" class="form-control">
                            </div>
                            <div class="form-group col-md-4" style="margin-top: 2rem">
                                <button type="submit" class="btn btn-primary float-right">Upload</button>
                            </div>
                        </div>
                    </form>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Upload Time</th>
                            <th>Name</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>

                        @if (count($data['data']) > 0)
                            @foreach ($data['data'] as $file)
                                <tr>
                                    <td>{{ $file['created_at'] }}<br>
                                        ({{$file['created_time'] }})
                                    </td>
                                    <td>{{ $file['name'] }}</td>
                                    <td>
                                        {{--<div class="progress">--}}
                                        {{--<div class="progress-bar" role="progressbar" style="width: {{ $item['progress_percentage'] }}%;" aria-valuenow="{{ $item['progress_percentage'] }}" aria-valuemin="0" aria-valuemax="100">{{ $item['progress_percentage'] }}%</div>--}}
                                        {{--</div>--}}
                                        {{ $file['status_label']}}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="2">No uploads available.</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

{{--<script>--}}
{{--    Echo.channel('progress-channel')--}}
{{--        .listen('ProgressUpdateEvent', (event) => {--}}
{{--            // Find the corresponding progress bar by file name or ID--}}
{{--            const fileName = event.progressData.file_name;--}}

{{--            // Calculate the new width for the progress bar--}}
{{--            const currentStep = event.progressData.current_step;--}}
{{--            const totalSteps = event.progressData.total_steps;--}}
{{--            const progressPercentage = (currentStep / totalSteps) * 100;--}}

{{--            // Update the corresponding progress bar--}}
{{--            const progressBar = document.querySelector(`#progress-bar-${fileName}`);--}}
{{--            progressBar.style.width = `${progressPercentage}%`;--}}
{{--            progressBar.innerHTML = `${progressPercentage.toFixed(2)}%`;--}}
{{--        });--}}
{{--</script>--}}

