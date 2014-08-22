
(function()
{
	tinymce.create('tinymce.plugins.timestreams',
	{
		

		timestreams : function(editor, url)
		{
			
				var button = editor.addButton('timestreams_button',
				{
					title : 'Insert Timestream',
					image : false,
					text : 'TS',
					
					onclick : function()
					{
						tb_show("Insert a Timestream Visualisation", "../wp-content/plugins/timestreams/admin/visualisation-picker.php?");
					}
				});
				return button;
			}
		
	});

	tinymce.PluginManager.add('timestreams_button', tinymce.plugins.timestreams);

	
})();


