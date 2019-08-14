<?php

class Ownerlistens_Messagemate_Adminhtml_OwnerlistensmessagemateController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('ownerlistens')
            ->_title($this->__('Index Action'));

        $mag_id = Mage::getStoreConfig('ownerlistens/config/mag_id');
        $mag_url = Mage::getBaseUrl();
        $mail = '';
        try {
            $mail = Mage::getStoreConfig('trans_email/ident_general/email');
        } catch (Exception $e) {
            // do nothing
        }
        $phone = '';
        try {
            $phone = Mage::getStoreConfig('general/store_information/phone');
        } catch (Exception $e) {
            // do nothing
        }
        $iframe_src = "//ownerlistens.com/magento/message_mate/";
        $iframe_src_params = "$iframe_src?mag_id=$mag_id&mag_url=$mag_url";
        if ($mail) $iframe_src_params .= "&em=$mail";
        if ($phone) $iframe_src_params .= "&ph=$phone";
        $html = "
        <script type='text/javascript'>
        var ol_handshake = setInterval(function () {
            var message = 'magento_message_mate_' + window.location.href;
            console.log('OwnerListens Message Mate:  sending message:  ' + message); //for debugging
            var target_url = /^https/.test(document.getElementById('ol_message_mate').src) ?
                'https://ownerlistens.com/magento/message_mate' : 'http://ownerlistens.com/magento/message_mate';
            var iframe = document.getElementById('ol_message_mate').contentWindow;
            iframe.postMessage(message, target_url); //send the message and target URI
        }, 2000);

        window.addEventListener('message',function(ev) {
            if (
                ev.origin !== 'http://demo.ownerlistens.com' && ev.origin !== 'http://ownerlistens.com' &&
                ev.origin !== 'https://demo.ownerlistens.com' && ev.origin !== 'https://ownerlistens.com'
            ) return;
            console.log('Received msg from OwnerListens: ' + ev.data);
            if(ev.data === 'magento handshake successful'){
                clearInterval(ol_handshake);
                return;
            }
            // ev.data is the script tag you should inject into the magento store
            var url = '" . Mage::helper('adminhtml')->getUrl('/ownerlistensmessagemate/savescript') . "';
            ajax(url, function(text, transport) {
                    var response = transport.responseText || 'result=failed&msg=Unexpected error';
                    var resp = parseQstr(response);
                    if (resp['result'] == 'success') {
                        var target_url = /^https/.test(document.getElementById('ol_message_mate').src) ?
                            'https://ownerlistens.com/magento/message_mate' : 'http://ownerlistens.com/magento/message_mate';
                        var iframe = document.getElementById('ol_message_mate').contentWindow;
                        iframe.postMessage('magento_message_mate success', target_url);
                    } else {
                        alert('Failed to save due to some server-side problem: ' + resp['msg']);
                    }
                }, 'mage_mate=' + encodeURIComponent(ev.data) + '&form_key=" . Mage::getSingleton("core/session")->getFormKey() . "', null);
        });
        function parseQstr(qstr) {
            var obj = {};
            var arr = qstr.split('&');
            for (var i = 0; i <= arr.length; ++i) {
                try {
                    arr[i] = arr[i].split('=');
                    obj[arr[i][0]] = arr[i][1];
                } catch (e) {
                    // do nothing
                }
            }
            return obj;
        }
        function ajax(url, callback, data, x) {
            try {
                x = new(this.XMLHttpRequest || ActiveXObject)('MSXML2.XMLHTTP.3.0');
                x.open(data ? 'POST' : 'GET', url, 1);
                x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                x.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                x.onreadystatechange = function () {
                    x.readyState > 3 && callback && callback(x.responseText, x);
                };
                x.send(data)
            } catch (e) {
                window.console && console.log(e);
            }
        };
        </script>
        <iframe id='ol_message_mate' style='width:100%;height:80vh;border:none;' src='$iframe_src_params'></iframe>
        ";

        $block = $this->getLayout()
            ->createBlock('core/text')
            ->setText($html);
        $this->_addContent($block);

        $this->renderLayout();
    }

    public function saveScriptAction()
    {
        error_log('Save called!');
        $this->getResponse()->setHeader('Content-type', 'text/plain');
        $mate = $this->getRequest()->getParam('mage_mate');
        try {
            error_log('Trying to update: ' . $mate);
            $resource = new Mage_Core_Model_Config();
            $resource->saveConfig('ownerlistens/config/message_mate', $mate, 'default', 0);
            Mage::app()->cleanCache();
            $this->getResponse()->setBody('result=success');
        } catch (Exception $e) {
            error_log('Exception thrown!');
            $this->getResponse()->setBody('result=fail&msg=' . $e->getMessage());
        }
    }
}
