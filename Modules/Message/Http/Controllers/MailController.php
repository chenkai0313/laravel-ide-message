<?php
/**
 * Created by PhpStorm.
 * User: yefan
 * Date: 2017/9/28
 * Time: 13:31
 */

namespace Modules\Message\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MailController extends Controller
{
    /**
     *
     */
    public function mailText(Request $request){
        $params = $request->input();
        $result = \MailService::sendText($params);
        return $result;
    }

    public function mailAttach(Request $request){
        $params = $request->input();
        $result = \MailService::sendAttachment($params);
        return $result;
    }
}