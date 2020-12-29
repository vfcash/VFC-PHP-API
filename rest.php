<?php

    error_reporting(E_ALL & ~E_NOTICE);

    //Start a session
    session_start();

    if(isset($_SESSION['lq']))
    {
        if(time() < $_SESSION['lq'])
        {
            echo "please wait " . abs( ($_SESSION['lq']-time()) ) . " seconds";
            exit;
        }
    }

    header("Access-Control-Allow-Origin: *");

    function getBalance($public_key)
    {
        $public_key = str_replace(' ', '', $public_key);
        if(strlen($public_key) > 12)
        {
            $na = shell_exec('/usr/bin/vfc ' . escapeshellarg($public_key));
            $p = strstr($na, "Final Balance: ");
            $p = str_replace("Final Balance: ", "", $p);
            return explode(" ", $p, 2)[0];
        }
        return 'invalid address';
    }

    if(isset($_GET['balance']))
    {
        $_SESSION['lq'] = time()+3;
        echo getBalance($_GET['balance']);
        $_SESSION['lq'] = time()+3;
        exit;
    }

    if(isset($_GET['difficulty']))
    {
        $_SESSION['lq'] = time()+3;
        echo explode("\n", str_replace("Difficulty: ", "", strstr(rtrim(shell_exec('/usr/bin/vfc difficulty')), "Difficulty: ")), 2)[0];
        $_SESSION['lq'] = time()+3;
        exit;
    }

    if(isset($_GET['findtrans']))
    {
        $_SESSION['lq'] = time()+16;
        $na = shell_exec('/usr/bin/vfc findtrans ' . escapeshellarg($_GET['findtrans']));
        echo $na;
        $_SESSION['lq'] = time()+16;
        exit;
    }
    if(isset($_GET['findtransjson']))
    {
        $_SESSION['lq'] = time()+16;
        $na = shell_exec('/usr/bin/vfc findtrans ' . escapeshellarg($_GET['findtransjson']));
        $p = explode(',', $na);
        echo '{"offset":'.$p[0].',"uid":'.$p[1].',"from":"'.$p[2].'","to":"'.$p[3].'","sig":"'.$p[4].'","amount":'.rtrim($p[5]).'}';
        $_SESSION['lq'] = time()+16;
        exit;
    }

    //Print Received Transactions for Public Key / Address
    if(isset($_GET['sent_transactions']))
    {
        $_SESSION['lq'] = time()+16;
        $out = '{"sent_transactions":[';
        $na = shell_exec('/usr/bin/vfc out ' . escapeshellarg($_GET['sent_transactions']));
        $p = explode("\n", $na);
        foreach($p as $pc)
        {
            if($pc == '')
                continue;
            $a = explode(' > ', $pc, 2);
            $out .= '{"addr":"' . $a[0] . '","amount":"' . number_format($a[1]) . '"},';
        }
        $out = rtrim($out, ",");
        $out .= ']}';
        echo $out;
        $_SESSION['lq'] = time()+16;
        exit;
    }

    //Print Sent Transactions for Public Key / Address
    if(isset($_GET['received_transactions']))
    {
        $_SESSION['lq'] = time()+16;
        $out = '{"received_transactions":[';
        $na = shell_exec('/usr/bin/vfc in ' . escapeshellarg($_GET['received_transactions']));
        $p = explode("\n", $na);
        foreach($p as $pc)
        {
            if($pc == '')
                continue;
            $a = explode(' > ', $pc, 2);
            $out .= '{"addr":' . $a[0] . '","amount":"' . number_format($a[1]) . '"},';
        }
        $out = rtrim($out, ",");
        $out .= ']}';
        echo $out;
        $_SESSION['lq'] = time()+16;
        exit;
    }

    function getPublicKey($private_key)
    {
        $na = shell_exec('/usr/bin/vfc getpub ' . escapeshellarg($private_key));
        $p = strstr($na, "Public: ");
        $p = str_replace("Public: ", "", $p);
        return explode("\n", $p, 2)[0];
    }

    //Get public key from private key
    if(isset($_GET['getpubkey']))
    {
        echo getPublicKey($_GET['getpubkey']);
        exit;
    }

    //Make Transaction Fast
    if(isset($_GET['fromprivfast']))
    {
        $_SESSION['lq'] = time()+12;
        exec('nohup /usr/bin/vfc ' . escapeshellarg($_GET['frompub']) . ' ' . escapeshellarg($_GET['topub']) . ' ' . escapeshellarg($_GET['amount']) . ' ' . escapeshellarg($_GET['fromprivfast']) . ' > /dev/null 2>&1 &');
        $_SESSION['lq'] = time()+12;
        exit;
    }

    //Receive raw transaction packet data encoded in base58, for receiving securely signed transactions
    if(isset($_GET['stp']))
    {
        $_SESSION['lq'] = time()+6;
        shell_exec('/usr/bin/vfc stp ' . escapeshellarg($_GET['stp']));
        //exec('nohup /usr/bin/vfc stp ' . escapeshellarg($_GET['stp']) . ' > /dev/null 2>&1 &');
        $_SESSION['lq'] = time()+6;
        exit;
    }
    //Receive raw packet data encoded in base64, for receiving securely signed transactions
    if(isset($_GET['stp64']))
    {
        $_SESSION['lq'] = time()+6;
        $packet = base64_decode($_GET['stp64']);
        if($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))
        {
            socket_sendto($socket, $packet, 147, 0, "127.0.0.1", 8787);
            echo "1";
        }
        else
            echo "0";
        $_SESSION['lq'] = time()+6;
        exit;
    }
    //Get transaction hash
    if(isset($_GET['uid']))
    {
        $_SESSION['lq'] = time()+1;
        echo rtrim(shell_exec('/usr/bin/vfc ' . escapeshellarg($_GET['frompub']) . ' ' . escapeshellarg($_GET['topub']) . ' ' . escapeshellarg($_GET['amount']) . ' ' . escapeshellarg($_GET['uid']) . ' GHSH'), "\n");
        $_SESSION['lq'] = time()+1;
        exit;
    }

    //Make Transaction
    if(isset($_GET['frompriv']))
    {
        $_SESSION['lq'] = time()+12;
        $frompub = getPublicKey($_GET['frompriv']);
        echo "<pre>" . shell_exec('/usr/bin/vfc ' . escapeshellarg($frompub) . ' ' . escapeshellarg($_GET['topub']) . ' ' . escapeshellarg($_GET['amount']) . ' ' . escapeshellarg($_GET['frompriv']));
        $_SESSION['lq'] = time()+12;
        exit;
    }

    //Get height
    if(isset($_GET['heigh']))
    {
        $_SESSION['lq'] = time()+3;
        $na = shell_exec('/usr/bin/vfc heigh');
        $p = strstr($na, "/ ");
        $p = str_replace('/ ', '', $p);
        echo explode(" ", $p, 2)[0];
        $_SESSION['lq'] = time()+3;
        exit;
    }
    if(isset($_GET['heighb']))
    {
        $_SESSION['lq'] = time()+3;
        $na = shell_exec('/usr/bin/vfc heigh');
        $p = strstr($na, "/ ");
        $p = str_replace('/ ', '', $p);
        echo (explode(" ", $p, 2)[0] * 144);
        $_SESSION['lq'] = time()+3;
        exit;
    }
    if(isset($_GET['heighkb']))
    {
        $_SESSION['lq'] = time()+3;
        $na = shell_exec('/usr/bin/vfc heigh');
        $p = strstr($na, "/ ");
        $p = str_replace('/ ', '', $p);
        echo (explode(" ", $p, 2)[0] * 144) / 1000;
        $_SESSION['lq'] = time()+3;
        exit;
    }
    if(isset($_GET['heighmb']))
    {
        $_SESSION['lq'] = time()+3;
        $na = shell_exec('/usr/bin/vfc heigh');
        $p = strstr($na, "/ ");
        $p = str_replace('/ ', '', $p);
        echo ((explode(" ", $p, 2)[0] * 144) / 1000) / 1000;
        $_SESSION['lq'] = time()+3;
        exit;
    }

    //Get circulating supply
    if(isset($_GET['circulating']))
    {
        $_SESSION['lq'] = time()+16;
        echo rtrim(shell_exec('/usr/bin/vfc circulating'));
        $_SESSION['lq'] = time()+16;
        exit;
    }

    //Get all transactions for an address
    if(isset($_GET['all']))
    {
        $_SESSION['lq'] = time()+16;
        echo '<pre>'.rtrim(shell_exec('/usr/bin/vfc all ' . escapeshellarg($_GET['all'])));
        $_SESSION['lq'] = time()+16;
        exit;
    }

    //new keypair from mnemonic
    if(isset($_GET['mnemonic']))
    {
        $_SESSION['lq'] = time()+3;
        $na = shell_exec('/usr/bin/vfc new ' . escapeshellarg(urldecode($_GET['mnemonic'])));
        $p = strstr($na, "Public: ");
        $p = str_replace('Public: ', '', $p);
        $public = explode("\n", $p, 2)[0];
        $p = strstr($na, "Private: ");
        $p = str_replace('Private: ', '', $p);
        $private = explode("\n", $p, 2)[0];
        if($private == "")
        {
            echo $na;
            exit;
        }
        echo '{"pub":"' . $public . '","priv":"' . $private . '"}';
        $_SESSION['lq'] = time()+3;
        exit;
    }

    //Print a fresh/new key pair
    if(isset($_GET['newkeypair']))
    {
        $_SESSION['lq'] = time()+3;
        $na = shell_exec('/usr/bin/vfc new');
        $p = strstr($na, "Public: ");
        $p = str_replace('Public: ', '', $p);
        $public = explode("\n", $p, 2)[0];
        $p = strstr($na, "Private: ");
        $p = str_replace('Private: ', '', $p);
        $private = explode("\n", $p, 2)[0];
        echo '{"pub":"' . $public . '","priv":"' . $private . '"}';
        $_SESSION['lq'] = time()+3;
        exit;
    }

    //Print a fresh/new key pair
    if(isset($_GET['newpriv']))
    {
        $_SESSION['lq'] = time()+3;
        $na = shell_exec('/usr/bin/vfc new');
        $p = strstr($na, "Private: ");
        $p = str_replace('Private: ', '', $p);
        echo explode("\n", $p, 2)[0];
        $_SESSION['lq'] = time()+3;
        exit;
    }

?>
