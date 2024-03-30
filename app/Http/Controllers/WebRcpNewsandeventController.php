<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WebRcpNewsandeventController
{
    function newsandevent(Request $request){
        $path = $request['pic']->store("newsandevent","public");
        DB::table('web_rcp_newsandevents')->insert([
            "name_category" => $request->name_category,
            "pic" => $path,
            "status" => "N"
        ]);
        return redirect(url('/admin/rcp/newsandevent'))->with(['inserted' => 'ok']);
    }
    function sl_all(){
        return DB::table('web_rcp_newsandevents')->select("*")
            ->orderBy('id', "desc")
            ->get();
    }
    function sl_by_id($id){
        return DB::table('web_rcp_newsandevents')->select("*")
            ->where("id", "=", $id)
            ->get();
    }
    function status(Request $request){
        DB::table('web_rcp_newsandevents')
            ->where("id" ,"=" , $request->_id)
            ->update([
                'status' => $request->_status
            ]);
        echo 1;
    }

    function del(Request $request){

        $path = DB::table('web_rcp_newsandevents')
            ->select('*')
            ->where('id', "=" , $request->_id)
            ->first();

        Storage::delete('public/'.$path->pic);


        DB::table('web_rcp_newsandevents')
            ->where("id" ,"=" , $request->_id)
           ->delete();

        DB::table('web_rcp_newsandevent_sub_texts')
            ->where('id_news', "=", $request->_id)
            ->delete();


        echo "<script>window.close()</script>";

    }
}
