@extends('layouts.teacherapp')
@section('htmlheader_title', 'Classes')
@section('main-content')
    <div class="container cleafix">
        <div class="spacingtop-bottom classes-page">
            @if ($message = Session::get('success'))
                <div id="successMessage">
                    <p class="alert alert-success">{{ $message }}<button type="button" class="close" data-dismiss="alert"
                            aria-hidden="true">×</button></p>
                </div>
                @php
                Session::forget('success');
                @endphp
            @endif

            @if ($message = Session::get('error'))
                <div id="successMessage">
                    <p class="alert alert-danger">{{ $message }}<button type="button" class="close" data-dismiss="alert"
                            aria-hidden="true">×</button></p>
                    @php
                    Session::forget('error');
                    @endphp
                </div>
            @endif
            <div class="ibox-content">
                <div class="clearfix top-button-box cf">
                    <div class="lefttitle-box">
                        <h3>{{ __('message.myclasses') }}</h3>
                    </div>
                    <!-- <span><button class="btn btncolor"><i class="fa fa-plus"></i> Add New Skill</button></span> -->
                </div>
                <div class="class-box">
                    <div class="classes blank">
                        <a href="{{ url('teacher/classesadd') }}" class="btn">
                            <i class="fa fa-plus-circle" aria-hidden="true"></i>{{ __('message.createnewclasses') }}
                        </a>
                    </div>
                    <?php if (!empty($classes)) {
                    foreach ($classes as $classe) { ?>
                    <div class="classes">
                        <div class="upper-section">
                            <div class="first">
                                <div class="title">
                                    <h5><?php echo $classe['name']; ?></h5>
                                    <a href="{{ url('teacher/editClass', $classe['id']) }}">
                                        <i class="fa fa-long-arrow-right" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ url('teacher/deleteclass', $classe['id']) }}"
                                        onclick="return confirm('Are you sure you want to delete this class ?')">
                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                    </a>
                                </div>
                                <ul>
                                    <li><?php echo $classe['grade']; ?></li>
                                    <li><?php echo $classe['subject']; ?></li>
                                </ul>
                            </div>
                            <div class="second">
                                <p><?php echo $classe['totalStudent']; ?>
                                    {{ __('message.students') }}
                                </p>

                                <a href="{{ url('teacher/teacherAddStudant', $classe['id']) }}">
                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                        <div class="lower-section">
                            <div class="assignments">
                                <div class="numbers"><?php echo $classe['totalAsignment']; ?>
                                </div>
                                <h6>{{ __('message.assignments') }}</h6>
                                <a href="{{ url('teacher/teacherAddAssignment', $classe['id']) }}">
                                    <i class="fa fa-plus" aria-hidden="true"></i>
                                </a>
                            </div>
                            <div class="recent">
                                <div class="recent_assign">
                                    <sub>{{ __('message.recentassignments') }}</sub>
                                    <p>{{ __('message.norecentassignments') }}</p>
                                </div>
                            </div>
                        </div>
                        <?php }
                        } ?>
                    </div>
                </div>
            </div>
        </div>
    @endsection
