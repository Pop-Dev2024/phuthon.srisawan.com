<?php

namespace App\Http\Controllers\appointment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\view_admin;
use App\Models\asfas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use MongoDB\Driver\Session;

class appointmentdoc extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return
     */
    public function index()
    {
        return view("newBackhouse.appointmentDoc");
    }

    function _all_category()
    {
        return view("newBackhouse.appointment._all_category");
    }

    function _edit_category()
    {
        return view("newBackhouse.appointment._edit_category");
    }

    function doctor(Request $request)
    {


        $department = DB::select('SELECT appointment_categories.id
      ,appointment_categories.name_cate

  FROM appointment_categories
  INNER JOIN  appointment_add_doc ON appointment_categories.id = appointment_add_doc._id_cate
  where  appointment_categories.status = :status
  group by  appointment_categories.id
      ,appointment_categories.name_cate
    order by appointment_categories.id DESC',['status'=>'Y']);
        $doctor = DB::table('appointment_add_doc')
            ->select('_name_doc', '_skill_doc', '_pic', '_language_doc', '_id_cate', 'id')
            ->where('_status', "=", 'Y')
            ->orderBy('_id_cate', "DESC")
            ->get();

        $data = [
            "doctor" => $doctor,
            "department" => $department
        ];
        return view('doctor', $data);
    }


    function _insert_cate(Request $request)
    {
        $pic_icon = $request->_piciconcate->store('appointmentDoc', 'public');
        $pic_cover = $request->_piccovercate->store('appointmentDoc', 'public');
        DB::table('appointment_categories')->insert([
            'name_cate' => $request->_namecate,
            'pic_icon' => $pic_icon,
            'pic_cover' => $pic_cover,
            'descript' => $request->descript,
        ]);
        return back()->with(['inserted' => 'ok']);
    }

    function _slall()
    {
        return DB::table('appointment_categories')->orderBy('id', "DESC")->get();
    }

    function _category_del(Request $request)
    {
        $_4delpic = DB::table('appointment_categories')->where('id', "=", $request->id)->first();
        $_sldocPic = DB::table('appointment_add_doc')->where('_id_cate', "=", $request->id)->get();

        foreach ($_sldocPic as $as_sldocPic) {
            Storage::delete('public/' . $as_sldocPic->_pic);
            DB::table('appointment_add_doc')->where('id', '=', $as_sldocPic->id)->delete();
            DB::table('appointment_add_slotdoc')->where('_id_doc', "=", $as_sldocPic->id)->delete();
        }
        Storage::delete('public/' . $_4delpic->pic_icon);
        Storage::delete('public/' . $_4delpic->pic_cover);

        DB::table('appointment_categories')->where('id', "=", $request->id)
            ->delete();


        $_sl_detail_pic = DB::table('appointment_cate_add')
            ->where('__id_cate', '=', $request->id)
            ->first();
        Storage::delete('public/' . $_sl_detail_pic->__pic);

        DB::table('appointment_cate_add')
            ->where('__id_cate', '=', $request->id)
            ->delete();

        return back()->with(['deleted' => 'ok']);
    }

    function _sl_id4edit($id)
    {
        return DB::table('appointment_categories')->where('id', "=", $id)->first();
    }

    function _edit_category_submit(Request $request)
    {
        $sql = DB::table('appointment_categories')->where('id', "=", $_GET['id'])->first();
        if (isset($request->_piciconcate)) {
            Storage::delete('public/' . $sql->pic_icon);
            $pic1 = $request->_piciconcate->store('appointmentDoc', 'public');
            DB::table('appointment_categories')->where('id', "=", $_GET['id'])->update([
                'pic_icon' => $pic1
            ]);
        }
        if (isset($request->_piccovercate)) {
            Storage::delete('public/' . $sql->pic_cover);
            $pic2 = $request->_piccovercate->store('appointmentDoc', 'public');
            DB::table('appointment_categories')->where('id', "=", $_GET['id'])->update([
                'pic_cover' => $pic2
            ]);
        }
        DB::table('appointment_categories')->where('id', "=", $_GET['id'])->update([
            'name_cate' => $request->_namecate,
            'status' => $request->_status,
            'descript' => $request->descript
        ]);
        return redirect(url('/admin/rcp/appointmentdoc/_all_category'));
    }

    function _edit_category_add_detail(Request $request)
    {
        $sl = DB::table('appointment_cate_add')
            ->where('__id_cate', '=', $_GET['id'])
            ->first();

        if ($sl === null) {
            $pic = $request->__pic->store('categories_detail', 'public');

            if ($request->__pic === null) {
                DB::table('appointment_cate_add')->insert([
                    '__id_cate' => $_GET['id'],
                    '__pic' => "",
                    '__text' => $request->__text,
                    '__status' => "Y"]);
            } else {
                DB::table('appointment_cate_add')->insert([
                    '__id_cate' => $_GET['id'],
                    '__pic' => $pic,
                    '__text' => $request->__text,
                    '__status' => "Y"
                ]);
            }
        } else {

            if ($request->__pic === null) {
                DB::table('appointment_cate_add')
                    ->where('__id_cate', '=', $_GET['id'])
                    ->update([
                        '__text' => $request->__text,
                    ]);
            } else {

                Storage::delete('public/' . $sl->__pic);

                $pic = $request->__pic->store('categories_detail', 'public');

                DB::table('appointment_cate_add')
                    ->where('__id_cate', '=', $_GET['id'])
                    ->update([
                        '__pic' => $pic,
                        '__text' => $request->__text,
                    ]);
            }
        }
        return redirect(url('/admin/rcp/appointmentdoc/_edit_category?id=' . $_GET['id']))->with(['success' => 'ok']);
    }

    function _sl_cate()
    {
        return DB::table('appointment_categories')->select('*')->get();
    }

    function _sl_detail_cate($id)
    {
        return DB::table('appointment_cate_add')
            ->where('__id_cate', '=', $id)
            ->first();
    }


    function _create_doc(Request $request)
    {
        $pic = $request->_pic_doc->store('appointmentDoc', 'public');
        DB::table('appointment_add_doc')->insert([
            '_id_cate' => $request->_category,
            '_name_doc' => $request->_name_doc,
            '_name_doc_en' => $request->_name_doc_en,
            '_skill_doc' => $request->_skill_doc,
            '_language_doc' => $request->_language_doc,
            '_pic' => $pic,
            '_education' => $request->educational,
            '_certification' => $request->_certification,
            '_club_members' => $request->_club_members,
            '_research_publishing' => $request->_research_publishing,
        ]);
        return redirect(url('/admin/rcp/appointmentdoc'))->with(['inserted' => 'ok']);
    }

    function _SlAllDoc()
    {
        return DB::table('appointment_add_doc')->orderBy('id', 'desc')->get();
    }

    function _SlCateDoc($id)
    {
        return DB::table('appointment_categories')->where('id', '=', $id)->first();
    }

    function _sl_4edit($id)
    {
        return DB::table('appointment_add_doc')->where('id', '=', $id)->first();
    }

    function _sl_4slot($id)
    {
        return DB::table('appointment_add_slotdoc')->where('_id_doc', '=', $id)->first();
    }

    function _chge_pic(Request $request)
    {
        $_delpic = DB::table('appointment_add_doc')->where('id', '=', $_GET['edit'])->first();
        Storage::delete('public/' . $_delpic->_pic);

        $_pic = $request->_chgepic->store('appointmentDoc', 'public');

        DB::table('appointment_add_doc')->where('id', '=', $_GET['edit'])->update([
            '_pic' => $_pic
        ]);
        return back()->with(['_chge_pic' => 'ok']);
    }

    function _edit_doc(Request $request)
    {
        DB::table('appointment_add_doc')->where('id', '=', $_GET['id'])->update([
            '_id_cate' => $request->_category,
            '_name_doc' => $request->_name_doc,
            '_name_doc_en' => $request->_name_doc_en,
            '_skill_doc' => $request->_skill_doc,
            '_language_doc' => $request->_language_doc,
            '_status' => $request->_status,
            '_education' => $request->educational,
            '_certification' => $request->_certification,
            '_club_members' => $request->_club_members,
            '_research_publishing' => $request->_research_publishing,
        ]);
        return redirect(url('/admin/rcp/appointmentdoc/_all_doc'))->with(['_chge_doc' => 'ok']);

    }

    function _del_doc(Request $request)
    {
        $delpic = DB::table('appointment_add_doc')->where('id', '=', $request->delid)->first();
        Storage::delete('public/' . $delpic->_pic);
        DB::table('appointment_add_doc')->where('id', '=', $request->delid)->delete();
        DB::table('appointment_add_slotdoc')->where('_id_doc', '=', $request->delid)->delete();
        DB::table('form_appointment_doc')->where('_id_doc', '=', $request->delid)->delete();

        return back();
    }

    function _sl_all_front()
    {
        return DB::table('appointment_categories')->where('status', '=', 'Y')->get();
    }

    function _sl_cate_4_home()
    {
        return DB::table('appointment_categories')
            ->where('status', '=', 'Y')
            ->orderBy('id', "desc")
            ->limit('3')
            ->get();
    }

    function _sl_all_id($id)
    {
        return DB::table('appointment_categories')
            ->where('status', '=', 'Y')
            ->where('id', '=', $id)
            ->first();
    }

    function _sl_doc($id)
    {
        return DB::table('appointment_add_doc')
            ->where('_id_cate', '=', $id)
            ->where('_status', '=', 'Y')
            ->get();
    }

    function _chkidget($id)
    {
        return DB::table('appointment_add_doc')->where('id', '=', $id)
            ->where('_status', '=', 'Y')
            ->first();
    }

    function _sl_cateid($id)
    {
        return DB::table('appointment_categories')->where('id', '=', $id)->first();

    }

    function _sl_doc4slot()
    {
        return DB::table('appointment_add_doc')->get();
    }

    function _chk_docinslot($id)
    {
        return DB::table('appointment_add_slotdoc')->where('_id_doc', '=', $id)->first();
    }

    function _add_slotdoc(Request $request)
    {

        $chksame = DB::table('appointment_add_slotdoc')->where('_id_doc', '=', $request->_id_doc)->first();

        if ($chksame === null) {
            if ($request->sun_stt === null) {
                $request->sun_stt = "00:00";
            }
            if ($request->sun_stp === null) {
                $request->sun_stp = "00:00";
            }
            if ($request->mon_stt === null) {
                $request->mon_stt = "00:00";
            }
            if ($request->mon_stp === null) {
                $request->mon_stp = "00:00";
            }
            if ($request->tues_stt === null) {
                $request->tues_stt = "00:00";
            }
            if ($request->tues_stp === null) {
                $request->tues_stp = "00:00";
            }
            if ($request->wednes_stt === null) {
                $request->wednes_stt = "00:00";
            }
            if ($request->wednes_stp === null) {
                $request->wednes_stp = "00:00";
            }
            if ($request->thurs_stt === null) {
                $request->thurs_stt = "00:00";
            }
            if ($request->thurs_stp === null) {
                $request->thurs_stp = "00:00";
            }
            if ($request->fri_stt === null) {
                $request->fri_stt = "00:00";
            }
            if ($request->fri_stp === null) {
                $request->fri_stp = "00:00";
            }
            if ($request->sat_stt === null) {
                $request->sat_stt = "00:00";
            }
            if ($request->sat_stp === null) {
                $request->sat_stp = "00:00";
            }

            DB::table('appointment_add_slotdoc')->insert([
                '_id_doc' => $request->_id_doc,
                "sun_stt" => $request->sun_stt,
                "sun_stp" => $request->sun_stp,
                "mon_stt" => $request->mon_stt,
                "mon_stp" => $request->mon_stp,
                "tues_stt" => $request->tues_stt,
                "tues_stp" => $request->tues_stp,
                "wednes_stt" => $request->wednes_stt,
                "wednes_stp" => $request->wednes_stp,
                "thurs_stt" => $request->thurs_stt,
                "thurs_stp" => $request->thurs_stp,
                "fri_stt" => $request->fri_stt,
                "fri_stp" => $request->fri_stp,
                "sat_stt" => $request->sat_stt,
                "sat_stp" => $request->sat_stp,
            ]);

            return redirect(url('/admin/rcp/appointmentdoc'))->with(['inserted' => 'ok']);

        } else {
            return redirect(url('/admin/rcp/appointmentdoc'));
        }
    }

    function _sl_slot($id)
    {
        return DB::table('appointment_add_slotdoc')->where('_id_doc', '=', $id)->first();
    }

    function _edit_slotdoc(Request $request)
    {
        if ($request->sun_stt === null) {
            $request->sun_stt = "00:00";
        }
        if ($request->sun_stp === null) {
            $request->sun_stp = "00:00";
        }
        if ($request->mon_stt === null) {
            $request->mon_stt = "00:00";
        }
        if ($request->mon_stp === null) {
            $request->mon_stp = "00:00";
        }
        if ($request->tues_stt === null) {
            $request->tues_stt = "00:00";
        }
        if ($request->tues_stp === null) {
            $request->tues_stp = "00:00";
        }
        if ($request->wednes_stt === null) {
            $request->wednes_stt = "00:00";
        }
        if ($request->wednes_stp === null) {
            $request->wednes_stp = "00:00";
        }
        if ($request->thurs_stt === null) {
            $request->thurs_stt = "00:00";
        }
        if ($request->thurs_stp === null) {
            $request->thurs_stp = "00:00";
        }
        if ($request->fri_stt === null) {
            $request->fri_stt = "00:00";
        }
        if ($request->fri_stp === null) {
            $request->fri_stp = "00:00";
        }
        if ($request->sat_stt === null) {
            $request->sat_stt = "00:00";
        }
        if ($request->sat_stp === null) {
            $request->sat_stp = "00:00";
        }
        DB::table('appointment_add_slotdoc')->where('_id_doc', '=', $_GET['id'])->update([
            "sun_stt" => $request->sun_stt,
            "sun_stp" => $request->sun_stp,
            "mon_stt" => $request->mon_stt,
            "mon_stp" => $request->mon_stp,
            "tues_stt" => $request->tues_stt,
            "tues_stp" => $request->tues_stp,
            "wednes_stt" => $request->wednes_stt,
            "wednes_stp" => $request->wednes_stp,
            "thurs_stt" => $request->thurs_stt,
            "thurs_stp" => $request->thurs_stp,
            "fri_stt" => $request->fri_stt,
            "fri_stp" => $request->fri_stp,
            "sat_stt" => $request->sat_stt,
            "sat_stp" => $request->sat_stp,
        ]);
        return back()->with(['ud' => 'ok']);
    }

    function _sl_doc3doc($id, $id_doc)
    {
        return DB::table('appointment_add_doc')
            ->where('_id_cate', '=', $id)
            ->where('id', '<>', $id_doc)
            ->limit(3)
            ->orderBy('id', 'desc')
            ->get();
    }

    function _add_form_appointment(Request $request)
    {
        DB::table('form_appointment_doc')->insert([
            '_id_doc' => $_GET['id'],
            '_sex' => $request->_sex,
            '_name' => $request->_name,
            '_born' => $request->_born,
            '_phone_number' => $request->_phone_number,
            '_date_appt' => $request->_date_appt
        ]);
        return back()->with(['inserted' => 'ok', '_date_appt' => $request->_date_appt]);
    }

    function _form_appointment_ajax_chekdate(Request $request)
    {
        $chk_null = DB::table('appointment_add_slotdoc')->where('_id_doc', '=', $request->_id_doc)->first();
        $_doc_detail = DB::table('appointment_add_doc')->where('id', '=', $request->_id_doc)->first();
        if ($chk_null === null) {
            echo json_encode([
                'status' => 'null',
                '_name_doc' => $_doc_detail->_name_doc
            ]);
        } else {
            $_data = [
                '0' => 'close',
                '1' => 'close',
                '2' => 'close',
                '3' => 'close',
                '4' => 'close',
                '5' => 'close',
                '6' => 'close'
            ];
            if ($chk_null->sun_stt != '00:00' && $chk_null->sun_stp != '00:00') {
                $_data[0] = 'open';
            }
            if ($chk_null->mon_stt != '00:00' && $chk_null->mon_stp != '00:00') {
                $_data[1] = 'open';
            }
            if ($chk_null->tues_stt != '00:00' && $chk_null->tues_stp != '00:00') {
                $_data[2] = 'open';
            }
            if ($chk_null->wednes_stt != '00:00' && $chk_null->wednes_stp != '00:00') {
                $_data[3] = 'open';
            }
            if ($chk_null->thurs_stt != '00:00' && $chk_null->thurs_stp != '00:00') {
                $_data[4] = 'open';
            }
            if ($chk_null->fri_stt != '00:00' && $chk_null->fri_stp != '00:00') {
                $_data[5] = 'open';
            }
            if ($chk_null->sat_stt != '00:00' && $chk_null->sat_stp != '00:00') {
                $_data[6] = 'open';
            }

            echo json_encode(['_day_chk' => $_data[$request->_days]]);
        }
    }

    function _sl_appointment()
    {
        return DB::table('form_appointment_doc')->where('_status', '=', "N")->get();
    }

    function _sl_namedoc($id)
    {
        return DB::table('appointment_add_doc')->where('id', '=', $id)->first();
    }

    function _approve_appoint(Request $request)
    {

        DB::table('form_appointment_doc')->where('id', '=', $_GET['id'])->update([
            '_status' => $request->_status,
            '_des_admin' => $request->_des_admin
        ]);
        return back()->with(['appt-approve' => 'ok']);
    }


    function _sl_appointment_approved()
    {
        return DB::table('form_appointment_doc')->where('_status', '=', "Y")->orderBy('id', 'desc')->get();
    }

    function _sl_appointment_reject()
    {
        return DB::table('form_appointment_doc')->where('_status', '=', "reject")->orderBy('id', 'desc')->get();
    }

    function _chk_approve($id)
    {
        return DB::table('form_appointment_doc')->where('id', '=', $id)->first();
    }

    function _sl_doc3($id)
    {
        return DB::table('appointment_add_doc')
            ->where('_id_cate', '=', $id)
            ->where('_status', 'Y')
            ->first();
    }

}
