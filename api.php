<?php

    //VF Cash PHP API v0.02

    //Before you can use this basic API you need to be running a local full-node and have UDP port 58008 forwarded on your router

    //get the balance of a public key / address
    function getBalance($public_key)
    {
        $na = shell_exec('/usr/bin/coin ' . escapeshellarg($public_key));
        $p = strstr($na, "is ");
        $p = str_replace('is ', '', $p);
        return explode(" ", $p, 2)[0];
    }

    //Make Transaction
    function makeTransaction($from_pub_key, $to_pub_key, $amount, $from_priv_key)
    {
        shell_exec('/usr/bin/coin ' . escapeshellarg($from_pub_key) . ' ' . escapeshellarg($to_pub_key) . ' ' . escapeshellarg($amount) . ' ' . escapeshellarg($from_priv_key));
    }

    //Get maximum supply
    function getMaxSupply()
    {
        return 4294967295;
    }

    //Get circulating supply
    function circulatingSupply()
    {
        $na = shell_exec('/usr/bin/coin out ' . escapeshellarg("foxXshGUtLFD24G9pz48hRh3LWM58GXPYiRhNHUyZAPJ"));

        $p = explode("\n", $na);
        $sp = 0;
        foreach($p as $pc)
        {
            if($pc == '')
                continue;

            $a = explode(' > ', $pc, 2);
            $sp += $a[1];
        }
        return number_format($sp);
    }

    //Print a fresh/new key pair
    function printKeyPair()
    {
        $na = shell_exec('/usr/bin/coin new');

        $p = strstr($na, "Public: ");
        $p = str_replace('Public: ', '', $p);
        $public = explode("\n", $p, 2)[0];

        $p = strstr($na, "Private: ");
        $p = str_replace('Private: ', '', $p);
        $private = explode("\n", $p, 2)[0];

        echo "<br><b>We have generated for you a new public and private key that can be used as a fresh account key-pair to transfer VFC assets to under your control.</b><br><br><b>Public Key</b><br><a href=\"#conv\">" . $public . "</a><br><br><b>Private Key</b> (KEEP PRIVATE, DO NOT SHARE)<br><a href=\"#conv\">" . $private . "</a><br><br>Please save these account details as these private and public keys are your only way of authorising actions on your VFC asset.";
    }

    //Print Received Transactions for Public Key / Address
    function printIns($public_key)
    {
        $na = shell_exec('/usr/bin/coin in ' . escapeshellarg($public_key));

        $p = explode("\n", $na);
        foreach($p as $pc)
        {
            if($pc == '')
                continue;

            $a = explode(' > ', $pc, 2);
            echo '<b>' . $a[0] . '</b> &gt; ' . number_format($a[1]) . '<br>';
        }
    }

    //Print Sent Transactions for Public Key / Address
    function printOuts($public_key)
    {
        $na = shell_exec('/usr/bin/coin out ' . escapeshellarg($public_key));

        $p = explode("\n", $na);
        foreach($p as $pc)
        {
            if($pc == '')
                continue;

            $a = explode(' > ', $pc, 2);
            echo '<b>' . $a[0] . '</b> &gt; ' . number_format($a[1]) . '<br>';
        }
    }

?>
