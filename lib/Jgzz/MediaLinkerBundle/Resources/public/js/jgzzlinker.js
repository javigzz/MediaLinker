
    window.addEventListener( 'load', windowLoadJgzzMediaLinkerScriptHandler, false );

    function windowLoadJgzzMediaLinkerScriptHandler() {

        /**
         * Media Panel async CRUD Manager
         * 
         * @param  {[type]} panel    HTML Element that holds the panel
         * @param  {[type]} options  
         */
        function mediaPanelManager(panel, options) {

            var s_current_panel = '.jzlink-panel-currents',
                s_form_panel = '.jzlink-panel-form',
                s_candidate_panel = '.jzlink-panel-candidates'
                ;

            this.panel = $(panel);
            this.panel_currents = this.panel.find(s_current_panel);
            this.panel_form = this.panel.find(s_form_panel);
            this.panel_candidates = this.panel.find(s_candidate_panel);
            this.urls = [];

            this.options = options;

            this.init = function(){
                if(this.panel_currents.length > 0){
                    this.init_panel('panel_currents');
                }
                if(this.panel_form.length > 0){
                    this.init_panel('panel_form');
                }
                if(this.panel_candidates.length > 0){
                    this.init_panel('panel_candidates');
                }
            };

            this.init_panel = function(name){
                if(name == 'panel_currents'){
                    this.panel_currents.find('.jzlink-linked-del-btn').click(this.actionHandler);
                    this.panel_currents.find('.jzlink-linked-unlink-btn').click(this.actionHandler);   
                    this.panel_currents.find('.jzlink-linked-action-btn').click(this.actionHandler);   
                }
                if(name == 'panel_candidates'){
                    this.panel_candidates.find('.jzlink-candidate-link-btn').click(this.actionHandler);
                }
                if(name == 'panel_form'){
                    this.panel_form.find('.jzlink-panel-form-form').submit(this.submit);
                }
            }

            var objeto = this;

            // handler del submit del formulario
            this.submit = function(event){
                event.preventDefault();

                // var form = $(event.target),
                var form = $(this),
                boton = form.find('input[type=submit]'),
                options = { 
                    invokedata: { manager: objeto },
                    success: function ( data ){
                        this.invokedata.manager.urls = data.urls;
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
                        if( textStatus == 'success' ){
                            if(data.result == 'ok' && $.inArray(data.action,['delete','unlink','link']) > -1){
                                this.invokedata.row.remove();
                            }
                            // update urls
                            this.invokedata.manager.urls = data.urls;
                            if(data.action == 'link'){
                                this.invokedata.manager.update_panel_currents();
                            }
                            if(data.action == 'unlink'){
                                this.invokedata.manager.update_panel_candidates();
                            }
                        }                        
                    },
                    complete: function (){
                        if(this.invokedata.row.length > 0){
                            this.invokedata.row.removeClass('disabled');
                            this.invokedata.row.css('cursor','pointer');
                        }
                    }
                });
            }

            this.updateform = function( data ){
                this.update_panel_html(this.panel_form, data.panel);
                this.update_panel_currents();
                this.init_panel('panel_form');
            };

            this.update_panel_currents = function(){
                this.fetch_panel('panel_currents');
            }

            this.update_panel_candidates = function(){
                this.fetch_panel('panel_candidates');
            }

            this.fetch_panel = function(panel_name){

                if(!this[panel_name].length > 0){
                    return;
                }

                url = this.urls[panel_name] || false;
                
                if(!url){
                   console.error('no panel url for panel ' + panel_name);
                   return;
                }

                var panel = $(this[panel_name][0]);

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
            var panelManagers = [];
            for (var i = paneles.length - 1; i >= 0; i--) {
            
                panelManagers.push(new mediaPanelManager(paneles[i],
                    { 'selector_row': '.jzlink-linked-entity-row' }));
            

            };
        }
    }