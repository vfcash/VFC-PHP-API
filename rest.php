<?php

    //Start a session
    session_start();

    if(isset($_SESSION['lq']))
    {
        if(time(0) < $_SESSION['lq'])
        {
            echo "please wait " . abs( ($_SESSION['lq']-time(0)) ) . " seconds";
            exit;
        }
    }

    header("Access-Control-Allow-Origin: *");

    if(isset($_GET['balance']))
    {
        $_SESSION['lq'] = time(0)+3;
        $na = shell_exec('/usr/bin/vfc ' . escapeshellarg($_GET['balance']));
        $p = strstr($na, "Final Balance: ");
        $p = str_replace("Final Balance: ", "", $p);
        echo explode(" ", $p, 2)[0];
        $_SESSION['lq'] = time(0)+3;
        exit;
    }

    //Print Received Transactions for Public Key / Address
    if(isset($_GET['sent_transactions']))
    {
        $_SESSION['lq'] = time(0)+16;
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
        $_SESSION['lq'] = time(0)+16;
        exit;
    }

    //Print Sent Transactions for Public Key / Address
    if(isset($_GET['received_transactions']))
    {
        $_SESSION['lq'] = time(0)+16;
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
        $_SESSION['lq'] = time(0)+16;
        exit;
    }

    function getBalance($public_key)
    {
        $na = shell_exec('/usr/bin/vfc ' . escapeshellarg($public_key));
        $p = strstr($na, "Final Balance: ");
        $p = str_replace("Final Balance: ", "", $p);
        return explode(" ", $p, 2)[0];
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

    //Make Transaction
    if(isset($_GET['frompriv']))
    {
        $_SESSION['lq'] = time(0)+3;
        $frompub = getPublicKey($_GET['frompriv']);
        $b0 = getBalance($frompub);
        shell_exec('/usr/bin/vfc ' . escapeshellarg($frompub) . ' ' . escapeshellarg($_GET['topub']) . ' ' . escapeshellarg($_GET['amount']) . ' ' . escapeshellarg($_GET['frompriv']));
        $b1 = getBalance($frompub);
        echo number_format($b0-$b1, 3, '.', '');
        $_SESSION['lq'] = time(0)+3;
        exit;
    }

    //Get height
    if(isset($_GET['heigh']))
    {
        $_SESSION['lq'] = time(0)+16;
        $na = shell_exec('/usr/bin/vfc heigh');
        $p = strstr($na, "/ ");
        $p = str_replace('/ ', '', $p);
        echo explode(" ", $p, 2)[0];
        $_SESSION['lq'] = time(0)+16;
        exit;
    }

    //Get circulating supply
    if(isset($_GET['circulating']))
    {
        $_SESSION['lq'] = time(0)+16;
        echo rtrim(shell_exec('/usr/bin/vfc circulating'));
        $_SESSION['lq'] = time(0)+16;
        exit;
    }

    //Print a fresh/new key pair
    if(isset($_GET['newkeypair']))
    {
        $_SESSION['lq'] = time(0)+3;
        $na = shell_exec('/usr/bin/vfc new');
        $p = strstr($na, "Public: ");
        $p = str_replace('Public: ', '', $p);
        $public = explode("\n", $p, 2)[0];
        $p = strstr($na, "Private: ");
        $p = str_replace('Private: ', '', $p);
        $private = explode("\n", $p, 2)[0];
        echo '{"pub":"' . $public . '","priv":"' . $private . '"}';
        $_SESSION['lq'] = time(0)+3;
        exit;
    }

?>
