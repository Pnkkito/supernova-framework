<?php

class Comentario extends Model {
	
	public static $primaryKey = "id";
	public static $displayField = "post_id";
	
	public static $belongsTo = array("Post");
	
}
