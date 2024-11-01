<h1>Writer Helper</h1>
<br/>
<p>
<?php 
	_e('Welcome to your new aid for writing and publishing books on your WordPress website','wtr_helper');
?>
</p>
<p>
<?php 
	_e('For more details, read our FAQ at','wtr_helper');
	echo "&nbsp;";
?>
<a href="https://writerhelper.clarissek.fr" target="_blank">https://writerhelper.clarissek.fr</a>
</p>
<br/>
<h2>
<?php
	_e('Getting started','wtr_helper');
?>
</h2>
<h4>
<?php 
	_e('Create your first book','wtr_helper');
?>
</h4>
<p>
<ul class="whList">
<?php 
	echo "<li>".__('Click on My Books menu','wtr_helper')."</li>";
	echo "<li>".__('Click on Create a new book button','wtr_helper')."</li>";
	echo "<li>".__("Enter your book's info",'wtr_helper')."</li>";
	echo "<li>".__('Create as many chapters as necessary','wtr_helper')."</li>";
	echo "<li>".__('Write your scenes','wtr_helper')."</li>";
?>
</ul>
</p><br/>
<h4>
<?php 
	_e('Generate an EPUB file','wtr_helper');
?>
</h4>
<p>
<ul class="whList">
<?php 
	echo "<li>".__('Click on My Books menu','wtr_helper')."</li>";
	echo "<li>".__('Click on Export to EPUB button','wtr_helper')."<br/>";
	echo "=> ".__('A new WordPress media has been created','wtr_helper')."</li>";
?>
</ul>
</p><br/>
<h2>
<?php 
	_e('About chapters and scenes statuses','wtr_helper');
?>
</h2>
<p>
<ul class="whList">
<?php 
	echo "<li>Draft: ".__("It's writing time.",'wtr_helper')."  ".
					   __("Write your scenes, reorder your chapters and scenes.",'wtr_helper')."</li>";
	echo "<li>ToEdit: ".__('Notify the end of drafting.','wtr_helper')."</li>";
	echo "<li>Editing: ".__("Proofread your scenes.",'wtr_helper')."</li>";
	echo "<li>Edited: ".__('Notify the end of editing.','wtr_helper')."</li>";
	echo "<li>ToPublish: ".__('Notify your publish intent.','wtr_helper')."</li>";
	echo "<li>Published: ".__('Your book and/or chapters can be seen on your website.','wtr_helper')."</li>";
	echo "<li>Trashed: ".__('Your book, chapter or scene is out but still recyclable.','wtr_helper')."</li>";
?>
</ul>
</p>
