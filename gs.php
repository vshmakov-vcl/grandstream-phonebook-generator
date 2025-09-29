<?php
require '/etc/freepbx.conf';                           // bootstrap FreePBX

//$provisioningRoot = "/var/www/provisioning";
$wwwroot = "/var/www";
$officeGroupDirectory = "provisioning";
$fleetGroupDirectory = "provisioning-fleet";
$phonebookfile = "phonebook.xml";
$phonebookfolder = "phonebooks" . DIRECTORY_SEPARATOR . "grandstream";
$FreePBX = FreePBX::Create();
$cm = $FreePBX->Contactmanager;

$groups = $cm->getGroups();
//var_dump($groups);
$targetGids;
foreach ($groups as $g) 
{
    $a['id'] = $g['id'];
    $a['name'] = $g['name'];
    $targetGids[] = $a;
}

//var_dump($targetGids);
if ($targetGids == null || count($targetGids) == 0) {
    die("No matching group\n");
}

$contactGroups;
foreach ($targetGids as $gid) 
    {
    	$contactGroups[$gid['name']] = $cm->getEntriesByGroupID($gid['id']);
    }

//var_dump($contactGroups);
foreach ($contactGroups as $key=>$group) {
    //var_dump($key);

    $xml = new DOMDocument( "1.0", "UTF-8" );
    $addressbook = $xml->createElement("AddressBook");

        foreach($group as $contact)
        {
            ///var_dump($contact);
            $cont = $xml->createElement("Contact");
            $cont->appendChild($xml->createElement("id", $contact['uid']));
            if(strlen($contact['fname']) == 0)
                {
                    $cont->appendChild($xml->createElement("FirstName", $contact['displayname']));
                } 
                else
                {
                    $cont->appendChild($xml->createElement("FirstName", $contact['fname']));
                }
            $cont->appendChild($xml->createElement("LastName", $contact['lname']));
            $phone = $xml->createElement("Phone");
            $phone->setAttribute("type", "Work");
            $phone->appendChild($xml->createElement('phonenumber', $contact['default_extension']));
            $phone->appendChild($xml->createElement('accountindex', '1'));
            $cont->appendChild($phone);
            $addressbook->appendChild($cont);
        }

    $xml->appendChild($addressbook);

// echo $key . PHP_EOL;
        
    try
    {
        switch($key) {
            case "Offices":
                $provisioningRoot = $wwwroot . DIRECTORY_SEPARATOR . $officeGroupDirectory;
                break;
            case "Fleet":
                $provisioningRoot = $wwwroot . DIRECTORY_SEPARATOR . $fleetGroupDirectory;
                break;
        }
 //       echo $provisioningRoot . PHP_EOL;

        if(!file_exists($provisioningRoot . DIRECTORY_SEPARATOR . $phonebookfolder))
        {
//            echo "Try to create " . $provisioningRoot . DIRECTORY_SEPARATOR . $phonebookfolder . "..." . PHP_EOL;
            mkdir($provisioningRoot . DIRECTORY_SEPARATOR . $phonebookfolder, 0755);
        }
        //echo "Try to save to " . $provisioningRoot . DIRECTORY_SEPARATOR . $phonebookfolder . DIRECTORY_SEPARATOR . $phonebookfile . "..." . PHP_EOL;
        $xml->save($provisioningRoot . DIRECTORY_SEPARATOR . $phonebookfolder . DIRECTORY_SEPARATOR . $phonebookfile);
    }
    catch (Exception $e)
    {
        echo $e->getMessage() . PHP_EOL;
    }
//    print $xml->saveXML();
}
