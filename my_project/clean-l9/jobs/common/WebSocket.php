<?php

class WebSocket
{

    /*
     * 根据客户端连接时发过来的消息提取Sec-WebSocket-Key
     */
    private function getHeaders($data)
    {
        $r = $h = $o = null;
        if(preg_match("/GET (.*) HTTP/"   , $data, $match))
        {
            $r = $match[1];
        }
        if(preg_match("/Host: (.*)\r\n/"  , $data, $match))
        {
            $h = $match[1];
        }
        if(preg_match("/Origin: (.*)\r\n/", $data, $match))
        {
            $o = $match[1];
        }
        if(preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $data, $match))
        {
            $key = $match[1];
        }
            
        return array($r, $h, $o, $key);
    }
    
    /*
     * 服务端根据Sec-WebSocket-Key生成握手数据
     */
    public function getHandShakeData($buffer)
    {
        list($resource, $host, $origin, $key) = $this->getHeaders($buffer);

        //websocket version 13
        $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
    
        $upgrade  = "HTTP/1.1 101 Switching Protocol\r\n" .
                "Upgrade: websocket\r\n" .
                "Sec-WebSocket-Version: 13\r\n".
                "Connection: Upgrade\r\n" .
                "Sec-WebSocket-Accept: " . $acceptKey . "\r\n\r\n";  //必须以两个回车结尾
    
        return $upgrade;
    }

    
    
//     private function doHandShake($user, $buffer)
//     {
//         list($resource, $host, $origin, $key) = $this->getHeaders($buffer);
    
//         //websocket version 13
//         $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
    
//         $upgrade  = "HTTP/1.1 101 Switching Protocol\r\n" .
//                 "Upgrade: websocket\r\n" .
//                 "Connection: Upgrade\r\n" .
//                 "Sec-WebSocket-Accept: " . $acceptKey . "\r\n\r\n";  //必须以两个回车结尾
        
//         $sent = socket_write($user->socket, $upgrade, strlen($upgrade));
//         $user->handshake=true;
//         return true;
//     }

    public function wrap($msg="", $opcode = 0x1)
    {
        //默认控制帧为0x1（文本数据）
        $firstByte = 0x80 | $opcode;
        $encodedata = null;
        $len = strlen($msg);
    
        if (0 <= $len && $len <= 125)
        {
            $encodedata = chr(0x81) . chr($len) . $msg;
        }
        else if (126 <= $len && $len <= 0xFFFF)
        {
            $low = $len & 0x00FF;
            $high = ($len & 0xFF00) >> 8;
            $encodedata = chr($firstByte) . chr(0x7E) . chr($high) . chr($low) . $msg;
        }
        else 
        {
            return chr(0x81).chr(127).pack("xxxxN", $len).$msg;
        }
        return $encodedata;
        
//         $fin=128;//0x80
//         $bufferLength = strlen($msg);
//         if ($bufferLength <= 125) 
//         {
//             $payloadLength = $bufferLength;
//             $payloadLengthExtended = '';
//             $payloadLengthExtendedLength = 0;
//         }
//         elseif ($bufferLength <= 65535) 
//         {
//             $payloadLength = 126;
//             $payloadLengthExtended = pack('n', $bufferLength);
//             $payloadLengthExtendedLength = 2;
//         }
//         else 
//         {
//             $payloadLength = 127;
//             $payloadLengthExtended = pack('xxxxN', $bufferLength); // pack 32 bit int, should really be 64 bit int
//             $payloadLengthExtendedLength = 8;
//         }
//         $buffer = pack('n', (($fin | $opcode) << 8) | $payloadLength) . $payloadLengthExtended . $msg;
//         return $buffer;
    }
    
    
    
    public function unwrap($msg="")
    {
        $opcode = ord(substr($msg, 0, 1)) & 0x0F;
        $payloadlen = ord(substr($msg, 1, 1)) & 0x7F;
        $ismask = (ord(substr($msg, 1, 1)) & 0x80) >> 7;
        $maskkey = null;
        $oridata = null;
        $decodedata = null;
    
        //数据不合法
        if ($ismask != 1 || $opcode == 0x8)
        {
            return false;
        }
    
        //获取掩码密钥和原始数据
        if ($payloadlen <= 125 && $payloadlen >= 0)
        {
            $maskkey = substr($msg, 2, 4);
            $oridata = substr($msg, 6);
        }
        else if ($payloadlen == 126)
        {
            $maskkey = substr($msg, 4, 4);
            $oridata = substr($msg, 8);
        }
        else if ($payloadlen == 127)
        {
            $maskkey = substr($msg, 10, 4);
            $oridata = substr($msg, 14);
        }
        $len = strlen($oridata);
        for($i = 0; $i < $len; $i++)
        {
            $decodedata .= $oridata[$i] ^ $maskkey[$i % 4];
        }
        return $decodedata;
    }
    

}

?>

