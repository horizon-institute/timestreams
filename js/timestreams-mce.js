
(function()
{
	tinymce.create('tinymce.plugins.timestreams',
	{
		createControl : function(id, controlManager)
		{
			if (id == 'timestreams_button')
			{
				var button = controlManager.createButton('timestreams_button',
				{
					title : 'Insert Timestream',
					image : '../wp-includes/images/smilies/icon_smile.gif',
					
					onclick : function()
					{
						tb_show("Insert a Timestream Visualisation", "../wp-content/plugins/timestreams/admin/visualisation-picker.php?");
					}
				});
				return button;
			}
			return null;
		}
	});

	tinymce.PluginManager.add('timestreams', tinymce.plugins.timestreams);
	
})()