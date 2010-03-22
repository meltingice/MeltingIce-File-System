<?php
session_start();
include_once('folderinfo.php');
include_once('fileinfo.php');

if($_POST['folderID'])
{
	$folder = getFolderInfo($_POST['folderID']);
}
?>

<?php if($_POST['modalbox']=='folder'): ?>
<h4 class="dialog_content-title">Folder Information</h4>
<div id="dialog_folderedit">
	<form id="dialog_foldereditform" name="dialog_foldereditform" action="javascript:editFolder()">
		<div class="clear">
			<p><span class="dialog_leftcontent">Folder Name:</span><span class="dialog_rightcontent"><input type="text" id="foldereditname" name="foldername" value="<?php echo $folder['name']; ?>" /></span></p>
		</div>
		<div class="clear">
			<p><span class="dialog_leftcontent">Permissions:</span><span class="dialog_rightcontent">
				<input type="radio" name="folderperms" value="public" <?php if($folder['perms']=='public'){ echo "checked"; } ?> /> Public
				<input type="radio" name="folderperms" value="private" <?php if($folder['perms']=='private'){ echo "checked"; } ?> /> Private
			</span></p>
		</div>
	</form>
</div>
<div id="dialog_folderinfo">
	<div class="clear">
		<p><span class="dialog_leftcontent">Created:</span><span id="dialog-foldercreated" class="dialog_rightcontent"><?php echo date('n/j/Y g:ia',$folder['created']); ?></span></p>
	</div>
	<div class="clear">
		<p><span class="dialog_leftcontent">Disk Usage:</span><span id="dialog-diskusage" class="dialog_rightcontent"><?php echo $folder['spaceUsed']." of ".getSpaceAvail(false,true); ?></span></p>
	</div>
</div>

<div id="dialog_buttons">
	<a href="javascript:saveFolderEdit(<?php echo $_POST['folderID']; ?>)"><img src="img/save.png" alt="Save" title="Save" /></a>
	<a href="javascript:deleteFolderConfirm(<?php echo $_POST['folderID']; ?>)"><img src="img/delete.png" alt="Delete" title="Delete" /></a>
</div>
<?php endif; ?>

<?php if($_POST['modalbox']=='deleteConfirm'): ?>
<p>Are you sure you want to delete:</p>
<p class="ui-dialog-delconfirm"><?php fileIDtoName($_POST['fileID']); ?></p>
<form action="home.php" method="post">
	<p><input type="button" value="Delete" onclick="deleteFile(<?php echo $_POST['fileID']; ?>)" />
	<input type="button" value="Cancel" onclick="$('#modalcontent').dialog('close');" /></p>
</form>
<?php endif; ?>

<?php if($_POST['modalbox']=="deleteFolderConfirm"): ?>
<p>Are you sure you want to delete:</p>
<p class="ui-dialog-delconfirm"><?php echo $folder['name']; ?></p>
<form action="home.php" method="post">
	<p><input type="button" value="Delete Folder, Keep Files" onclick="deleteFolder(<?php echo $_POST['folderID']; ?>,'keep')" />
	<input type="button" value="Delete Folder and Files" onclick="deleteFolder(<?php echo $_POST['folderID']; ?>,'delete')" />
	<input type="button" value="Cancel" onclick="$('#modalcontent').dialog('close');" /></p>
</form>
<?php endif; ?>

<?php if($_POST['modalbox']=='crossload'): ?>
<?php include_once('foldermgmt.php'); ?>

<p style="margin-top: 15px;">Enter URL to file:</p>
<form action="javascript:crossloadFile()" method="post" id="crossload-form">
	<p><input type="text" id="crossload-url" /></p>
	<p><select name="crossload-folder" id="crossload-folder">
		<?php
			$folders = getUserFolderArray();
			if(count($folders)>0)
			{
				foreach($folders as $folder)
				{
					echo "<option value='".$folder['folderID']."'>".$folder['foldername']."</option>\n";
				}
			}
			echo "<option value='-1'>Uncategorized</option>\n";
		?>
	</select></p>
	<p><input type="button" id="crossload-submit" value="Crossload" onclick="crossloadFile()" /></p>
</form>
<?php endif; ?>

<?php if($_POST['modalbox']=='myAccount'&&$_SESSION['username']==$_SESSION['activeUser']): ?>
<?php include('account.php'); ?>
<h4>General Options</h4>
<ul id="account-settings">
	<li onmouseover="showClickChange(this)" onmouseout="hideClickChange()">Upload method: <span id="account_upload"><?php echo getUploadMethod(); ?></span></li>
</ul>
<h4>Change Password</h4>

<?php endif; ?>