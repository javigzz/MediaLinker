
    window.addEventListener( 'load', windowLoadJgzzMediaLinkerScriptHandler, false );

    function windowLoadJgzzMediaLinkerScriptHandler() {

        /**
         * Panel async CRUD Manager
         * 
         * @param  {[type]} panel    HTML Element that holds the panel
         * @param  {[type]} options  
         */
        function mediaPanelManager(panel, options) {

            this.mainpanel = $(panel);
            this.panel_currents = null;
            this.panel_form = null;
            this.panel_candidates = null;
            var manager = this;
            // this.options = options;

            /**
             * Initialize de panels found on place
             */
            this.init = function(){
                var map = { 
                    'panel_currents': '.jzlink-panel-currents',
                    'panel_form': '.jzlink-panel-form',
                    'panel_candidates': '.jzlink-panel-candidates'
                };
                var manager = this;
                $.each(map, function(pname,css){
                    var panel = manager.mainpanel.find(css);
                    if (panel.length == 1){
                        manager[pname] = panel; 
                        manager.init_panel(pname);
                    }
                });
            };

            /**
             * Initialize a panel by name
             * 
             * @param  {[type]} panel_name [description]
             * @return {[type]}            [description]
             */
            this.init_panel = function(panel_name){
                this.mainpanel.find('.jzlink-call').click(this.actionHandler);
                this.mainpanel.find('.jzlink-updpanel').click(this.updateCallHandler);
                if(panel_name == 'panel_form'){
                    this.panel_form.find('.jzlink-panel-form-form').submit(this.submit);
                }
            }

            var objeto = this;

            // handler del submit del formulario
            this.submit = function(event){
                event.preventDefault();
                var form = $(this),
                boton = form.find('input[type=submit]'),
                options = { 
                    invokedata: { manager: objeto },
                    success: function ( data ){
                        this.invokedata.manager.updateform( data );
                    },
                    complete: function (data){
                        boton.prop("disabled", false);
                    }
                };

                boton.prop("disabled", true);

                // ver http://www.malsup.com/jquery/form/#ajaxSubmit
                form.ajaxSubmit(options);

                return false; 
            };

            /**
             * Handles a remote call action
             * 
             * @param  {[type]} event
             */
            this.actionHandler = function(event){
                event.preventDefault();
                var link = $(this),
                url = link.attr('href'),
                row = [];

                if (typeof url == 'undefined') return false;

                row = $(link.closest('.jzlink-entity-row'));
                row.css('cursor','progress');
                row.addClass('disabled');

                $.ajax({
                    invokedata: { manager: objeto, row: row },
                    url: url,
                    success: function( data, textStatus, jqXHR ){
                        if( textStatus != 'success' ){
                            throw new Error("status "+textStatus+" in success response...");
                        }
                        if(data.result == 'ok' && $.inArray(data.action,['delete','unlink','link']) > -1){
                            this.invokedata.row.remove();
                        }
                        if(data.action == 'link'){
                            this.invokedata.manager.update_panel_currents();
                        }
                        if(data.action == 'unlink'){
                            this.invokedata.manager.update_panel_candidates();
                        }
                        // throw new Error("Unknow action "+data.action+" received in succesfull response");
                    },
                    complete: function (){
                        if(this.invokedata.row.length > 0){
                            this.invokedata.row.removeClass('disabled');
                            this.invokedata.row.css('cursor','pointer');
                        }
                    }
                });
            }

            /**
             * Explicit call to update a panel
             * 
             * @return {[type]} [description]
             */
            this.updateCallHandler = function(event){
                event.preventDefault();
                manager.update_panel($(this).data('panel'));
            }

            this.updateform = function( data ){
                this.update_panel_html(this.panel_form, data.panel);
                this.update_panel_currents();
                this.init_panel('panel_form');
            };

            this.update_panel_currents = function(){
                this.update_panel('panel_currents');
            }

            this.update_panel_candidates = function(){
                this.update_panel('panel_candidates');
            }

            /**
             * Fetches an updated panel from the server
             * 
             * @param  {string} panel_name
             */
            this.update_panel = function(panel_name){

                if(this[panel_name] == null){
                    throw new Error('There is no panel: '+panel_name);
                }

                url = this[panel_name].data('update-url');
                
                if(!url){
                    throw new Error('No url for updating panel ' + panel_name);
                }

                var panel = $(this[panel_name]);

                $.ajax({
                    type: 'get',
                    url: url,
                    invokedata: { manager: objeto, panel: panel, panel_name: panel_name },
                    success: function( data, textStatus, jqXHR ){
                        if( textStatus == 'success' ){
                            this.invokedata.panel.html(data);
                            this.invokedata.manager.init_panel(this.invokedata.panel_name);
                        }
                    }
                });
            };

            this.update_panel_html = function(panel, htmlcont){
                //todo: get decoded html from 'data' (in server side)
                var decohtml = $($("<div/>").html(htmlcont).text()).html();
                panel.html(decohtml);
            };

            this.init();
        };

        var paneles = $('.jzlink-panel');

        if(paneles.length > 0){
            var jgzzLPM = [];
            window.jgzzLPM = jgzzLPM;
            for (var i = paneles.length - 1; i >= 0; i--) {
            
                jgzzLPM.push(new mediaPanelManager(paneles[i],
                    { 'selector_row': '.jzlink-linked-entity-row' }));
            };
        }
    }