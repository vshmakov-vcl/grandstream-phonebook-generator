<?php
require '/etc/freepbx.conf';                           // bootstrap FreePBX

$provisioningRoot = "/var/www/provisioning";
$phonebookfile = "gs-phonebook.xml";
$phonebookfolder = "phonebooks";
$FreePBX = FreePBX::Create();
$cm = $FreePBX->Contactmanager;

$groups = $cm->getGroups();
$targetGids;
foreach ($groups as $g) 
{
    $targetGids[] = $g['id'];
}
if ($targetGids == null || count($targetGids) == 0) {
    die("No matching group\n");
}

$contactGroups;
foreach ($targetGids as $gid) 
    {
    	$contactGroups[] = $cm->getEntriesByGroupID($gid);
    }
$xml = new DOMDocument( "1.0", "UTF-8" );
$addressbook = $xml->createElement("AddressBook");
foreach ($contactGroups as $group) {
    foreach($group as $contact)
    {
        //var_dump($contact);
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
        $phone = $xml->createElement("Phone" );
        $phone->setAttribute("type", "Work");
        $phone->appendChild($xml->createElement('phonenumber', $contact['default_extension']));
        $phone->appendChild($xml->createElement('accountindex', '1'));
        $cont->appendChild($phone);
        $addressbook->appendChild($cont);
    }
}
$xml->appendChild($addressbook);
try
{
    if(!file_exists($provisioningRoot . DIRECTORY_SEPARATOR . $phonebookfolder))
    {
        mkdir($provisioningRoot . DIRECTORY_SEPARATOR . $phonebookfolder, 0755);
    }
    $xml->save($provisioningRoot . DIRECTORY_SEPARATOR . $phonebookfolder . DIRECTORY_SEPARATOR . $phonebookfile);
}
catch (Exception $e)
{
    echo $e->getMessage();
}
//print $xml->saveXML();
