<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\DataTables\StudentReportDataTable;
use App\Http\DataTables\TeacherClsProSetDataTable;
use App\Http\DataTables\TeacherstudentassignsDataTable;
use App\Mail\Adminemail;
use App\Mail\StudentEmail;
use App\Problemset;
use App\Question;
use App\Skill;
use App\Student;
use App\Studentans;
use App\SupriClassesStudent;
use App\TeacherClasses;
use App\TeacherClassesStuAsign;
use App\TeacherClassesStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Session;

class TeacherClassesController extends Controller
{
    public function index()
    {
        session()->forget('teacher.test');
        //session()->forget('students.name');
        $classes = DB::table('classes')->where(['status' => 'Active', 'created_id' => session()->get('teacher_id')])->orderBy('created_at', 'desc')->get();
        $classretVal = array();
        if (!empty($classes)) {
            foreach ($classes as $classe) {
                $rowVal = array();
                $rowVal["id"] = $classe->id;
                $rowVal["name"] = $classe->name;
                $rowVal["code"] = $classe->code;
                $clsStd = DB::table('standard')->where(['status' => 'Active', 'id' => $classe->grade_id])->get();
                $rowVal["grade"] = isset($clsStd[0]->title) ? $clsStd[0]->title : '';
                $subjStd = DB::table('subject')->where(['status' => 'Active', 'id' => $classe->subject_id])->get();
                $rowVal["subject"] = isset($subjStd[0]->title) ? $subjStd[0]->title : '';

                $teatcherId = session()->get('teacher_id');

                $recentProlemsetOfClass = DB::table('classes_students_assignment')->where(['class_id' => $classe->id, 'teacher_id' => $teatcherId, 'status' => 'Active'])->latest()->limit(1)->first();

                $rowVal["recentAssignment"] = findProblemset($recentProlemsetOfClass->pro_set_id ?? 0)->probset_name ?? __('message.norecentassignments');

                //$rowVal["totalStudent"] = $this->totalStudent($classe->id);
                $rowVal["totalStudent"] = $this->totalStudent($classe->code);
                $rowVal["totalAsignment"] = $this->totalAsignment($classe->id);

                /*$teatcherId = session()->get('teacher_id');
                $teacherResceAsignDesc = DB::table('classes_students_assignment')->where(['class_id' => $classe->id,'teacher_id'=>$teatcherId,'status'=>'Active'])->orderBy('created_at', 'desc')->limit(1)->first(['created_at']);
                $createdat = $teacherResceAsignDesc->created_at;*/
                //exit;
                //$teacherResceAsign = DB::table('classes_students_assignment')->where(['class_id' => $classe->id,'teacher_id'=>$teatcherId,'status'=>'Active','created_at'=>$createdat])/*->where('created_at','=',$teacherResceAsignDesc->created_at)*/->get(['id']);
                /*print_r($teacherResceAsign);
                exit;*/
                //$totRecentAssignment = count($teacherResceAsign);
                //$rowVal["totalRecentAsignment"] = ($totRecentAssignment>0) ? $totRecentAssignment : "No";
                $classretVal[] = $rowVal;
            }
            //dd($classretVal);
        }

        return view('teacher.classes.index', ["classes" => $classretVal]);
    }
    public function studntsreport(Request $request, $id)
    {
        if (!empty($id)) {
            //dd('i am here');
            $classEdit = DB::table('classes')->where(['status' => 'Active', 'id' => $id])->first();
            $teacherId = session()->get('teacher_id');
            $classDetail["teacher_id"] = $teacherId;
            $classDetail["id"] = $classEdit->id;
            $classDetail["name"] = $classEdit->name;
            session()->put('class_name', $classEdit->name);
            session()->put('class_id', $classEdit->id);
            $classDetail["code"] = $classEdit->code;
            $classStds = TeacherClassesStudent::where(['teacher_id' => $teacherId, 'class_id' => $classEdit->id, 'status' => 'Active'])->get(['student_id']);
            $classStdsArr = $classStds->toArray();
            $classDetail["studentIdsCheck"] = array_column($classStdsArr, 'student_id');
            $classDetail["start_date"] = $classEdit->start_date;
            $classDetail["end_date"] = $classEdit->end_date;
            //$classDetail["totalStudent"] = $this->totalStudent($classEdit->id);
            $classDetail["totalStudent"] = $this->totalStudent($classEdit->code);
            $clsStd = DB::table('program')->where(['status' => 'Active', 'id' => $classEdit->program_id])->first();
            $classDetail["program"] = isset($clsStd->title) ? $clsStd->title : '';
            $cls_Std = DB::table('standard')->where(['status' => 'Active', 'id' => $classEdit->grade_id])->first();
            $classDetail["grade"] = isset($cls_Std->title) ? $cls_Std->title : '';
            $classDetail["grade_id"] = isset($cls_Std->id) ? $cls_Std->id : '';
            $subjStd = DB::table('subject')->where(['status' => 'Active', 'id' => $classEdit->subject_id])->first();
            $classDetail["subject"] = isset($subjStd->title) ? $subjStd->title : '';

            $teacherAsign = TeacherClassesStuAsign::where(['class_id' => $id, 'teacher_id' => $teacherId, 'status' => 'Active'])->get();

            $assignIaArray = [];
            foreach ($teacherAsign as $asign) {
                $assignIaArray[] = $asign->pro_set_id;
            }

            $problemsets_teacher = Problemset::whereIn('id', $assignIaArray)->get(['probset_name', 'id']);

            $problemsets_grade = Problemset::where('standard_id', $classDetail["grade_id"])->get(['probset_name', 'id']);

            $problemsets_teacher_array = (array) $problemsets_teacher;
            $problemsets_grade_array = (array) $problemsets_grade;

            $result = array_map(function ($problemsets_teacher_array, $problemsets_grade_array) {
                return array_merge(isset($problemsets_teacher_array) ? $problemsets_teacher_array : array(), isset($problemsets_grade_array) ? $problemsets_grade_array : array());
            }, $problemsets_teacher_array, $problemsets_grade_array);

            $problemsetId = [];
            foreach ($result as $key => $set) {
                foreach ($set as $v) {
                    $problemsetId[] = $v['id'];
                }
            }

            $teacherStd = Student::where(['class_code' => $classEdit->code, 'status' => '1'])->get(['first_name', 'last_name', 'id']);

            $studentReport = $this->studentReportResponce($teacherStd, $problemsetId);

            return view('teacher.classes.studentreport', compact('classDetail', 'problemsets_teacher', 'teacherStd', 'studentReport', 'problemsets_grade'));
        }

    }
    public function studntsreportfilter(Request $request)
    {
        if ($request->ProblemsetId == '') {
            $teacherId = session()->get('teacher_id');
            $teacherAsign = TeacherClassesStuAsign::where(['class_id' => $request->classId, 'teacher_id' => $teacherId, 'status' => 'Active'])->get();
            $assignIaArray = [];
            foreach ($teacherAsign as $asign) {
                $assignIaArray[] = $asign->pro_set_id;
            }
            $problemsets = Problemset::whereIn('id', $assignIaArray)->get(['probset_name', 'id']);
            $problemsetId = [];
            foreach ($problemsets as $set) {
                $problemsetId[] = $set->id;
            }
        } else {
            $problemsetId[] = $request->ProblemsetId;
        }
        $classEdit = DB::table('classes')->where(['status' => 'Active', 'id' => $request->classId])->first();
        $teacherStd = Student::where(['class_code' => $classEdit->code, 'status' => '1'])->get(['first_name', 'last_name', 'id']);
        $studentReport = $this->studentReportResponce($teacherStd, $problemsetId);
        return view('teacher.classes.report-list', compact('studentReport'))->render();
    }
    public function studentReportResponce($teacherStd, $problemsetId)
    {
        $studentReport = [];

        foreach ($teacherStd as $key => $value) {

            //get student first name and last name
            $studentReport[$key]['id'] = $value->id;
            $studentReport[$key]['name'] = $value->first_name . ' ' . $value->last_name;

            //get assignment result
            $correct_ans = problemWiseStudentCorrectqueCount($value->id, $problemsetId);
            $total_ques = problemWiseStudentTotalqueCount($problemsetId);
            $assinment_result = ($correct_ans / $total_ques) * 20;
            $studentReport[$key]['assinment_result'] = $assinment_result;

            //Remidiation Result
            $remidiation_ans = problemWiseRemidiationTotalCount($value->id, $problemsetId);
            $remidiation_result = (($correct_ans + $remidiation_ans) / $total_ques) * 20;
            $studentReport[$key]['remidiation_result'] = $remidiation_result;

            //Master Skill
            $mastered_skill = number_format($correct_ans * 100 / $total_ques, 0);
            $studentReport[$key]['mastered_skill'] = $mastered_skill;

            //Acquired skill
            $acqired_skill = number_format($remidiation_ans * 100 / $total_ques, 0);
            $studentReport[$key]['acqired_skill'] = $acqired_skill;

            //Remaining gap
            $remaining_gaps = number_format(100 - $mastered_skill - $acqired_skill, 0);
            $studentReport[$key]['remaining_gaps'] = $remaining_gaps;

            $studentReport[$key]['problemsetId'] = $problemsetId;
        }
        return $studentReport;
    }

    public function isStudentResultAvailable(Request $request)
    {
        $stud_id = $request->studId;
        $prob_id = $request->probId;
        $class_name = session()->get('class_name');
        $class_id = session()->get('class_id');
        $id = $prob_id;
        $stuassignincmlt = __('message.stuassignincmlt');
        $problemset = Problemset::find($prob_id);
        if (!empty($problemset)) {
            $totalproblem = count($problemset->question_id);
            $studentid = $stud_id;
            $totalans = Studentans::where('student_id', $studentid)->where('prob_id', $problemset->id)->where('student_assignment_status', 1)->count();

            $studentdata = Student::where('id', $studentid)->first();
            $first_name = $studentdata->first_name;
            $last_name = $studentdata->last_name;

            if ($totalproblem === $totalans) {
                return response()->json([
                    'success' => true,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $stuassignincmlt,
                ]);
            }
        } else {
            return false;
        }

    }

    public function studentresult($stud_id, $prob_id, StudentReportDataTable $table)
    {
        $class_name = session()->get('class_name');
        $class_id = session()->get('class_id');
        $id = $prob_id;
        $problemset = Problemset::find($prob_id);
        $stuassignincmlt = __('message.stuassignincmlt');
        if (!empty($problemset)) {
            $totalproblem = count($problemset->question_id);
            $studentid = $stud_id;
            $totalans = Studentans::where('student_id', $studentid)->where('prob_id', $problemset->id)->where('student_assignment_status', 1)->count();

            $studentdata = Student::where('id', $studentid)->first();
            $first_name = $studentdata->first_name;
            $last_name = $studentdata->last_name;

            if ($totalproblem === $totalans) {
                $probset_name = $problemset->probset_name;
                $probset_id = $problemset->id;

                return $table->with(['qns_id' => $problemset->question_id, 'studentid' => $studentid, 'prid' => $problemset->id])->render('teacher.classes.studentresult', compact('probset_name', 'probset_id', 'id', 'studentid', 'first_name', 'last_name', 'class_name', 'class_id'));

            } else {

                //session()->put('assign_not_cmplt',1);

                return redirect()->back()->with('error', $stuassignincmlt);

            }
        }
    }
    public function addclass()
    {
        $programs = DB::table('program')->where('status', 'Active')->get();
        $subjects = DB::table('subject')->where('status', 'Active')->get();
        $standards = DB::table('standard')->where('status', 'Active')->get();
        $tags = DB::table('tags')->where('status', 'Active')->get();
        return view('teacher.classes.addedit', ['programs' => $programs, 'subjects' => $subjects, 'standards' => $standards, 'tags' => $tags, 'classEdit' => array("id" => "", "name" => "", "start_date" => "", "end_date" => "", "program_id" => "", "grade_id" => "", "subject_id" => "", "tag_id" => "")]);
    }
    public function editclass($id)
    {
        if (!empty($id)) {
            $classEdit = DB::table('classes')->where(['status' => 'Active', 'id' => $id])->get();
            $retrunArr["id"] = $classEdit[0]->id;
            $retrunArr["name"] = $classEdit[0]->name;
            $retrunArr["start_date"] = $classEdit[0]->start_date;
            $retrunArr["end_date"] = $classEdit[0]->end_date;
            $retrunArr["program_id"] = $classEdit[0]->program_id;
            $retrunArr["grade_id"] = $classEdit[0]->grade_id;
            $retrunArr["subject_id"] = $classEdit[0]->subject_id;
            $retrunArr["tag_id"] = $classEdit[0]->tag_id;
            $programs = DB::table('program')->where('status', 'Active')->get();
            $subjects = DB::table('subject')->where('status', 'Active')->get();
            $standards = DB::table('standard')->where('status', 'Active')->get();
            $tags = DB::table('tags')->where('status', 'Active')->get();
            return view('teacher.classes.addedit', ['programs' => $programs, 'subjects' => $subjects, 'standards' => $standards, 'tags' => $tags, 'classEdit' => $retrunArr]);
        }
    }
    public function store(Request $request)
    {
        $validate = $this->validate($request, [
            'class_name' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'program' => 'required',
            'grades' => 'required',
            'subject' => 'required',
        ], [
            'class_name.required' => 'Please enter class name.',
            'start_date.required' => 'Please select start date.',
            'end_date.required' => 'Please select end date.',
            'program.required' => 'Please select grades.',
            'grades.required' => 'Please select program.',
            'subject.required' => 'Please select subject.',
        ]);
        if (!empty($request->post('editId'))) {
            $TeacherClassesUpd = TeacherClasses::where('id', $request->post('editId'))->update([
                'name' => $request->post('class_name'),
                'start_date' => $request->post('start_date'),
                'end_date' => $request->post('end_date'),
                'program_id' => $request->post('program'),
                'grade_id' => $request->post('grades'),
                'subject_id' => $request->post('subject'),
                'tag_id' => $request->post('tags'),
                'status' => 'Active',
            ]);
            return redirect()->route('teacherclasses.view')->with('success', 'Class updated Successfully');
        } else {
            $ranodmCode = substr(md5(time()), 0, 8);
            $userId = session()->get('teacher_id');
            $TeacherClassesAdd = TeacherClasses::create([
                'name' => $request->post('class_name'),
                'code' => $ranodmCode,
                'start_date' => $request->post('start_date'),
                'end_date' => $request->post('end_date'),
                'program_id' => $request->post('program'),
                'grade_id' => $request->post('grades'),
                'subject_id' => $request->post('subject'),
                'tag_id' => $request->post('tags'),
                'created_id' => $userId,
                'status' => 'Active',
            ]);
            return redirect()->route('teacherclasses.view')->with('success', 'Class Create Successfully');
        }
    }

    public function getStdVal(Request $request)
    {
        if ($request->ajax()) {
            $whereArr["status"] = "Active";
            $proId = $request->post('pro_id');
            if (!empty($proId)) {
                $whereArr["program_id"] = $proId;
            }
            $stdAjx = DB::table('standard')->where($whereArr)->get();
            $returnMsg = "<option value='' data-badge=''>Select Grades</option>";
            if (!empty($stdAjx)) {
                foreach ($stdAjx as $stds) {
                    $returnMsg .= "<option value='" . $stds->id . "' data-badge='" . $stds->title . "'>" . $stds->title . "</option>";
                }
            }
            return $returnMsg;
        }
    }

    public function teacherAddStudant(TeacherstudentassignsDataTable $table, $id)
    {
        if (!empty($id)) {
            //dd('i am here');
            $classEdit = DB::table('classes')->where(['status' => 'Active', 'id' => $id])->first();
            $teacherId = session()->get('teacher_id');
            $classDetail["teacher_id"] = $teacherId;
            $classDetail["id"] = $classEdit->id;
            $classDetail["name"] = $classEdit->name;
            $classDetail["code"] = $classEdit->code;
            $classStds = TeacherClassesStudent::where(['teacher_id' => $teacherId, 'class_id' => $classEdit->id, 'status' => 'Active'])->get(['student_id']);
            $classStdsArr = $classStds->toArray();
            $classDetail["studentIdsCheck"] = array_column($classStdsArr, 'student_id');
            $classDetail["start_date"] = $classEdit->start_date;
            $classDetail["end_date"] = $classEdit->end_date;
            //$classDetail["totalStudent"] = $this->totalStudent($classEdit->id);
            $classDetail["totalStudent"] = $this->totalStudent($classEdit->code);
            $clsStd = DB::table('program')->where(['status' => 'Active', 'id' => $classEdit->program_id])->first();
            $classDetail["program"] = isset($clsStd->title) ? $clsStd->title : '';
            $clsStd = DB::table('standard')->where(['status' => 'Active', 'id' => $classEdit->grade_id])->first();
            $classDetail["grade"] = isset($clsStd->title) ? $clsStd->title : '';
            $subjStd = DB::table('subject')->where(['status' => 'Active', 'id' => $classEdit->subject_id])->first();
            $classDetail["subject"] = isset($subjStd->title) ? $subjStd->title : '';

            $studentAssignInClass = TeacherClassesStudent::where('teacher_id', '=', $teacherId)->where('status', '=', 'Active')->where('class_id', '!=', $classEdit->id)->get(['student_id']);
            $classStdsNotArr = $studentAssignInClass->toArray();
            $classAsignStds = array_column($classStdsNotArr, 'student_id');

            $studentAssignInSupClass = SupriClassesStudent::where('status', '=', 'Active')->get(['student_id']);
            $supriClassStdsNotArr = $studentAssignInSupClass->toArray();
            $superiStdArr = array_column($supriClassStdsNotArr, 'student_id');

            $finalStdArr = array_merge($superiStdArr, $classAsignStds);
            $students = DB::table('students')->whereNotIn('id', $finalStdArr)->get();

            if (!empty($classDetail["studentIdsCheck"])) {
                foreach ($classDetail["studentIdsCheck"] as $value) {
                    session()->push('students.name', $value);
                }
            }
            return $table->with(['stu_id' => $finalStdArr, 'check_id' => $classDetail["studentIdsCheck"], 'class_code' => $classDetail["code"]])->render('teacher.classes.addstudant', compact('classDetail', 'students'));
        }
    }
    public function showStudents(Request $request)
    {
        $json = array();
        if (isset($request->search['value'])) {
            $sql = Student::select('*')
                ->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search['value'] . '%')
                        ->orWhere('email', 'like', '%' . $request->search['value'] . '%');
                })
                ->orderBy($request->columns[$request->order[0]['column']]['name'], $request->order[0]['dir']);

            $recordsTotal = $sql->count();
            $data = $sql->limit($request->length)->skip($request->start)->get();

            $sql1 = Student::select('*')
                ->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search['value'] . '%')
                        ->orWhere('email', 'like', '%' . $request->search['value'] . '%');
                })
                ->orderBy($request->columns[$request->order[0]['column']]['name'], $request->order[0]['dir']);
            $data1 = $sql1->get();
            $recordsFiltered = count($data1);
            $json['data'] = $data;
            $json['draw'] = $request->draw;
            $json['recordsTotal'] = $recordsTotal;
            $json['recordsFiltered'] = $recordsFiltered;
        } else {
            $sql = Student::select('*')
                ->orderBy($request->columns[$request->order[0]['column']]['name'], $request->order[0]['dir']);
            $recordsTotal = $sql->count();
            $data = $sql->limit($request->length)->skip($request->start)->get();
            $json['data'] = $data;
            $json['draw'] = $request->draw;
            $json['recordsTotal'] = $recordsTotal;
            $json['recordsFiltered'] = $recordsTotal;
        }
        return json_encode($json);
    }
    public function addTeacherStudent(Request $request)
    {
        if (!empty($request->post('teacher_id'))) {
            $classStds = TeacherClassesStudent::where(['teacher_id' => $request->post('teacher_id'), 'class_id' => $request->post('class_id'), 'status' => 'Active'])->get(['id']);
            $allAssStdId = $classStds->toArray();
            $allAssStdIdAsso = array_column($allAssStdId, 'id');
            $htmlStdArr = teacherstusessiom_array();
            //dd($htmlStdArr);
            if (!empty($htmlStdArr)) {
                for ($i = 0; $i < count($htmlStdArr); $i++) {

                    $studentAssignInClass = TeacherClassesStudent::where('teacher_id', '=', $request->post('teacher_id'))->where('status', '=', 'Active')->where('class_id', '!=', $request->post('class_id'))->get(['student_id']);
                    $classStdsNotArr = $studentAssignInClass->toArray();
                    $classAsignStds = array_column($classStdsNotArr, 'student_id');

                    $studentAssignInSupClass = SupriClassesStudent::where('status', '=', 'Active')->get(['student_id']);
                    $supriClassStdsNotArr = $studentAssignInSupClass->toArray();
                    $superiStdArr = array_column($supriClassStdsNotArr, 'student_id');

                    $finalStdArr = array_merge($superiStdArr, $classAsignStds);
                    //dd($htmlStdArr);
                    if (!in_array($htmlStdArr[$i], $finalStdArr)) {
                        if (!empty($allAssStdIdAsso[$i])) {
                            $TeacherClsStd = TeacherClassesStudent::where('id', $allAssStdIdAsso[$i])->update([
                                'student_id' => $htmlStdArr[$i],
                                'class_id' => $request->post('class_id'),
                                'teacher_id' => $request->post('teacher_id'),
                                'status' => 'Active',
                            ]);
                        } else {
                            $TeacherClsStd = TeacherClassesStudent::create([
                                'student_id' => $htmlStdArr[$i],
                                'class_id' => $request->post('class_id'),
                                'teacher_id' => $request->post('teacher_id'),
                                'status' => 'Active',
                            ]);
                        }
                        unset($allAssStdIdAsso[$i]);
                    }
                }
            }
            if (!empty($allAssStdIdAsso)) {
                foreach ($allAssStdIdAsso as $key => $value) {
                    $TeacherClsStd = TeacherClassesStudent::where('id', $value)->update([
                        'status' => 'Inactive',
                    ]);
                }
            }
            return redirect()->route('teacherclasses.view')->with('success', 'Class updated Successfully');
        } else {
            return redirect()->route('teacher.view');
        }
    }
    public function addTeacherMultiStudent(Request $request)
    {
        if (!empty($request->post('studemts_email'))) {
            $subscribeEmail = explode(";", $request->post('studemts_email'));
            for ($i = 0; $i < count($subscribeEmail); $i++) {
                $stdEmial = Student::where([])->get(['email']);
                $stdEmialArr = $stdEmial->toArray();
                $stdEmialArrAsso = array_column($stdEmialArr, 'email');
                if (!in_array($subscribeEmail[$i], $stdEmialArrAsso)) {
                    $fields = [
                        'first_name' => "",
                        'last_name' => "",
                        'user_name' => $subscribeEmail[$i],
                        'email' => $subscribeEmail[$i],
                        'password' => bcrypt($subscribeEmail[$i]),
                        'nivue' => "",
                        'school' => "",
                    ];
                    $res = Student::create($fields);
                    if ($res) {
                        try {
                            Mail::to($res->email)->send(new StudentEmail($res, $subscribeEmail[$i]));
                            Mail::to(env('MAIL_USERNAME'))->send(new Adminemail($res));
                        } catch (Throwable $e) {
                        }
                        $teacherId = session()->get('teacher_id');
                        $TeacherClsStd = TeacherClassesStudent::create([
                            'student_id' => $res->id,
                            'class_id' => $request->post('class_id'),
                            'teacher_id' => $teacherId,
                            'status' => 'Active',
                        ]);
                    }
                }
            }
            return redirect()->route('teacherclasses.teacherAddStudant', $request->post('class_id'))->with('success', 'Class updated Successfully');
        } else {
            return redirect()->route('teacher.view');
        }
    }
/* public function totalStudent($clsId) {
if(!empty($clsId)) {
$teatcherId = session()->get('teacher_id');
$teacherStd = TeacherClassesStudent::where(['class_id' => $clsId,'teacher_id'=>$teatcherId,'status'=>'Active'])->get();
return count($teacherStd);

}
} */
    public function totalStudent($clsCode)
    {
        if (!empty($clsCode)) {
            $teacherStd = Student::where(['class_code' => $clsCode, 'status' => '1'])->get();
            return count($teacherStd);

        }
    }
/* Assignment related functions */
    public function teacherAddAssignment(Request $request, TeacherClsProSetDataTable $table, $id)
    {
        if (!empty($id)) {
            $classEdit = DB::table('classes')->where(['status' => 'Active', 'id' => $id])->first();
            $teacherId = session()->get('teacher_id');
            $retrunArr["teacher_id"] = $teacherId;
            $retrunArr["id"] = $classEdit->id;
            $retrunArr["name"] = $classEdit->name;
            // $request->session()->put('reclassid',$classEdit->id);
            session(['reclassid' => $classEdit->id]);
            //return view('teacher.classes.assignment',['classDetail'=>$retrunArr]);
            return $table->with(['class_id' => $id, "type" => "teacher", "userid" => $teacherId])->render('teacher.classes.assignment', ['classDetail' => $retrunArr]);
        }
    }

    public function addTeacherStuAsignment(Request $request)
    {
        if (!empty($request->post('problem_set'))) {
            $classStdsAsign = TeacherClassesStuAsign::where(['teacher_id' => $request->post('teacher_id'), 'class_id' => $request->post('class_id'), 'status' => 'Active'])->get(['id']);
            $allAssAsingId = $classStdsAsign->toArray();
            $allAssAsingId = array_column($allAssAsingId, 'id');
            $htmlProSetArr = $request->post('problem_set');
            for ($i = 0; $i < count($htmlProSetArr); $i++) {
                if (!empty($allAssAsingId[$i])) {
                    TeacherClassesStuAsign::where('id', $allAssAsingId[$i])->update([
                        'pro_set_id' => $htmlProSetArr[$i],
                        'class_id' => $request->post('class_id'),
                        'teacher_id' => $request->post('teacher_id'),
                        'status' => 'Active',
                    ]);
                } else {
                    TeacherClassesStuAsign::create([
                        'pro_set_id' => $htmlProSetArr[$i],
                        'class_id' => $request->post('class_id'),
                        'teacher_id' => $request->post('teacher_id'),
                        'status' => 'Active',
                    ]);
                }
                unset($allAssAsingId[$i]);
            }
            if (!empty($allAssAsingId)) {
                foreach ($allAssAsingId as $key => $value) {
                    TeacherClassesStuAsign::where('id', $value)->update([
                        'status' => 'Inactive',
                    ]);
                }
            }
            $classId = $request->post('class_id');
            return view('teacher.classes.assignmetsuccess', compact('classId'));
        } else {
            return redirect()->route('teacherclasses.teacherAddAssignment', $request->post('class_id'));
        }
    }
    public function editTeacherStuAsignment($id)
    {
        $questionData = array();
        if (!empty($id)) {
            $ProblemsetQue = Problemset::where(['id' => $id])->first(['question_id']);
            $questionData = implode(',', $ProblemsetQue->question_id);
            /*if(!empty($ProblemsetQue->question_id)) {
        $questionData = Question::select('title')->whereIn('id', $ProblemsetQue->question_id)->pluck('title')->toArray();
        }*/
        }
        return view('teacher.classes.assignmetDetail', compact('questionData'));
    }
    public function showAssignmentQue(Request $request)
    {
        $json = array();
        if (isset($request->search['value'])) {
            $sql = Question::select('*')->where('status', 1)->whereIn('id', explode(",", $request->post('questionsIds')))
                ->where(function ($query) use ($request) {
                    $query->where('title', 'like', '%' . $request->search['value'] . '%')
                        ->orWhere('description', 'like', '%' . $request->search['value'] . '%')
                        ->orWhere('question', 'like', '%' . $request->search['value'] . '%');
                });

            $recordsTotal = $sql->count();
            $data = $sql->limit($request->length)->skip($request->start)->orderBy($request->columns[$request->order[0]['column']]['name'], $request->order[0]['dir'])->get();

            foreach ($data as $key => $value) {
                $skill = json_decode($value->skill, true);
                $skillData = Skill::select('title')->whereIn('id', $skill)->pluck('title')->toArray();
                $data[$key]->skill = implode(',', $skillData);

                if (!empty(sessiom_array())) {
                    $data[$key]->qnsid = json_encode(sessiom_array());
                } else {
                    $data[$key]->qnsid = '';
                }
            }

            $sql1 = Question::select('*');
            $data1 = $sql1->orderBy($request->columns[$request->order[0]['column']]['name'], $request->order[0]['dir'])->get();
            $recordsFiltered = count($data1);
            $json['data'] = $data;
            $json['draw'] = $request->draw;
            $json['recordsTotal'] = $recordsTotal;
            $json['recordsFiltered'] = $recordsFiltered;
        } else {
            $sql = Question::select('*')->where('status', 1)->whereIn('id', explode(",", $request->post('questionsIds')));
            $recordsTotal = $sql->count();
            $data = $sql->orderBy($request->columns[$request->order[0]['column']]['name'], $request->order[0]['dir'])->limit($request->length)->skip($request->start)->get();

            foreach ($data as $key => $value) {
                $skill = json_decode($value->skill, true);
                if (!empty($skill)) {
                    $skillData = Skill::select('title')->whereIn('id', $skill)->pluck('title')->toArray();

                    $data[$key]->skill = implode(',', $skillData);
                } else {
                    $data[$key]->skill = '';
                }
                if (!empty(sessiom_array())) {
                    $data[$key]->qnsid = json_encode(sessiom_array());
                } else {
                    $data[$key]->qnsid = '';
                }
            }
            $json['data'] = $data;
            $json['draw'] = $request->draw;
            $json['recordsTotal'] = $recordsTotal;
            $json['recordsFiltered'] = $recordsTotal;
        }
        return json_encode($json);
    }
    public function totalAsignment($clsId)
    {
        if (!empty($clsId)) {
            $teatcherId = session()->get('teacher_id');
            $teacherAsign = TeacherClassesStuAsign::where(['class_id' => $clsId, 'teacher_id' => $teatcherId, 'status' => 'Active'])->get(['id']);
            return count($teacherAsign);

        }
    }

    public function getStudentassign(Request $request)
    {
        $id = $request->get('id');
        $ischecked = $request->get('ischecked');
        if ($id && $ischecked === 'true') {
            session()->push('students.name', $id);
        } else {
            $product = teacherstusessiom_array();
            if (($key = array_search($id, $product)) !== false) {
                unset($product[$key]);
                session()->put('students.name', $product);
            }
            $delete = TeacherClassesStudent::where('student_id', $id)->delete();
        }
        if (!empty(teacherstusessiom_array())) {
            return response()->json(['success' => true, "checkbox_count" => count(teacherstusessiom_array())]);
        } else {
            return response()->json(['success' => false, "checkbox_count" => 0]);
        }
    }

    public function getstudentproblemassign(Request $request)
    {
        if ($request->post('assign') == 0) {
            if (!empty($request->post('problem_set'))) {
                $classStdsAsign = TeacherClassesStuAsign::where(['teacher_id' => $request->post('teacher_id'), 'class_id' => $request->post('class_id'), 'pro_set_id' => $request->post('problem_set'), 'status' => 'Active'])->first();
                if (!empty($classStdsAsign)) {
                    TeacherClassesStuAsign::where('id', $classStdsAsign->id)->update([
                        'pro_set_id' => $request->post('problem_set'),
                        'class_id' => $request->post('class_id'),
                        'teacher_id' => $request->post('teacher_id'),
                        'status' => 'Active',
                    ]);
                } else {
                    TeacherClassesStuAsign::create([
                        'pro_set_id' => $request->post('problem_set'),
                        'class_id' => $request->post('class_id'),
                        'teacher_id' => $request->post('teacher_id'),
                        'status' => 'Active',
                    ]);
                }
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false]);
            }
        } else {
            $classStdsAsign = TeacherClassesStuAsign::where(['teacher_id' => $request->post('teacher_id'), 'class_id' => $request->post('class_id'), 'pro_set_id' => $request->post('problem_set'), 'status' => 'Active'])->delete();
            if ($classStdsAsign) {
                return response()->json(['success' => true]);
            }
        }
        /*$id = $request->get('id');
    $ischecked = $request->get('ischecked');
    if ($id && $ischecked === 'true') {
    session()->push('teacherstudentsproblem.name', $id);
    } else {
    $product = teacherstusessiom_array();
    if (($key = array_search($id, $product)) !== false) {
    unset($product[$key]);
    session()->put('teacherstudentsproblem.name', $product);
    }
    $delete = TeacherClassesStudent::where('student_id', $id)->delete();
    }
    if (!empty(teacherstusessiom_array())) {
    return response()->json(['success' => true, "checkbox_count" => count(teacherstusessiom_array())]);
    } else {
    return response()->json(['success' => false, "checkbox_count" => 0]);
    }*/
    }

/*   public function getteachProbsetassign(Request $request){
$id=$request->get('id');
$ischecked=$request->get('ischecked');
if($id && $ischecked==='true'){
session()->push('students.name',$id);
}else{
$product=teacherstusessiom_array();
if(($key=array_search($id,$product))!== false) {
unset($product[$key]);
session()->put('students.name',$product);
}
$delete=TeacherClassesStudent::where('student_id',$id)->delete();
}
if(!empty(teacherstusessiom_array())){
return response()->json(['success'=>true,"checkbox_count"=>count(teacherstusessiom_array())]);
}else{
return response()->json(['success'=>false,"checkbox_count"=>0]);
}
}*/

    public function deleteclass($id)
    {
        try
        {
            if (!empty($id)) {
                $teatcherId = session()->get('teacher_id');
                $teacherclassdelete = TeacherClasses::find($id);
                $teacherclassstud = TeacherClassesStudent::where('teacher_id', $teatcherId)->where('class_id', $id)->delete();
                $teacherclassstudassigm = TeacherClassesStuAsign::where('teacher_id', $teatcherId)->where('class_id', $id)->delete();
                $teacherclassdelete->delete();
                return redirect()->route('teacherclasses.view')->with('success', 'Class delete successfully');

            } else {
                return redirect()->route('teacherclasses.view')->with('error', 'Class not deleted');
            }

        } catch (Exception $e) {
            $message = $e . getMessage();
            return redirect()->route('teacherclasses.view')->with('error', $message);
        }
    }

    public function teacherRemoveStudant($id)
    {
        $student = Student::find($id ?? 0);
        $student->class_code = '';
        $student->update();
        return redirect()->back();
    }
}
