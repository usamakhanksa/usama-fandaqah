/**
 * @Todo : Refactoring
 */

Nova.booting((Vue, router, store) => {
    router.afterEach((to, from) => {
        $(window).arrive('.view_reservation' , function (){
            element = $(this);

            element.on('click' , function(e){
                e.preventDefault();
                let id = $(this).attr('data-attr-id') ;

                Nova.app.$router.push('/reservation/' + id) ;
            })

        });
        $(window).arrive('.view_customer' , function (){
            element = $(this);

            element.on('click' , function(e){
                e.preventDefault();
                let id = $(this).attr('data-attr-id') ;

                Nova.app.$router.push('/resources/customers/' + id) ;
            })

        });
        // General Delete Modal
        $(window).arrive('button[type="submit"]#confirm-delete-button' , function (){
            element = $(this);
            element.html(Nova.app.__('Confirm Delete')) ;
        });



        // prepare the injected li in Units and it's card resources
        let injectedLi = `<li id="injectedLi" class="breadcrumbs__item"><a onclick="Nova.app.$router.push('/resources/units')" class="router-link-active cursor-pointer">${Nova.app.__('Units')}</a></li>`;
        // initially remove the injected if found
        let injectedLiInReports = `<li id="injectedLiInReports" class="breadcrumbs__item"><a onclick="Nova.app.$router.push('/dashboard/reports')" class="router-link-active cursor-pointer">${Nova.app.__('Reports')}</a></li>`;

        $(document).arrive("ul.breadcrumbs", function() {
            $('#injectedLi').remove();
            $('#injectedLiInReports').remove();
        });





        if ('resourceName' in router.app._route.params) {
            const name = router.app._route.params.resourceName;

            switch (name) {

                case 'highlights':
                    if(to.name == 'custom-index'){

                        $(document).arrive('h1.mb-3.text-90.font-normal.text-2xl' , function(){
                            let element = $(this) ;
                            element.text(Nova.app.__('Highlights'))
                        });

                        $(document).arrive("a.btn.btn-default.btn-primary.btn-default.btn-primary", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add new Highlight'));
                        });
                        $(document).arrive("h3.text-base.text-80.font-normal.mb-6", function() {
                            let element = $(this);
                            element.text(Nova.app.__('No Highlights added yet'));
                        });

                        $(document).arrive("a.btn.btn-sm.btn-outline.inline-flex.items-center", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add new Highlight'));
                        });

                        $(document).arrive("div.flex.items-center.ml-auto.px-3" , function(){
                            $(this).remove();
                        });


                    }
                    if(to.name == 'custom-create'){

                        $(document).arrive('h1.mb-3.text-90.font-normal.text-2xl' , function(){
                            let element = $(this) ;
                            element.text(Nova.app.__('Add new Highlight'))
                        });

                        $(document).arrive("button.btn.btn-default.btn-primary.inline-flex.items-center.relative", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add new Highlight'));
                        });

                        $(document).arrive("button.btn.btn-default.btn-primary.inline-flex.items-center.relative.mr-3", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Save & Add Another'));
                        });

                    }

                    if(to.name == 'detail'){


                        $(document).arrive('h4.text-90.font-normal.text-2xl.flex-no-shrink' , function(){
                            let element = $(this) ;
                            element.text(Nova.app.__('Highlight Details'))
                        });
                        $(document).arrive('a.btn.btn-default.btn-icon.bg-primary' , function(){
                            let element = $(this) ;
                            element.addClass('custom-mr-2');
                        });

                    }

                    if(to.name == 'custom-edit'){

                        $(document).arrive('h1.mb-3.text-90.font-normal.text-2xl' , function(){
                            let element = $(this) ;
                            element.text(Nova.app.__('Update Highlight'))
                        });

                        $(document).arrive("button.btn.btn-default.btn-primary.inline-flex.items-center.relative", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Update Highlight'));
                        });

                        $(document).arrive("button.btn.btn-default.btn-primary.inline-flex.items-center.relative.mr-3", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Update & Continue Editing'));
                        });

                    }

                    break;
                case 'teams' :

                    if(to.name == 'custom-edit'){
                        $(document).arrive('h1.mb-3.text-90.font-normal.text-2xl' , function(){
                            let element = $(this) ;
                            element.text(Nova.app.__('Update Facility Information'))
                        });

                        $(document).arrive("button[type='submit']", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Save'));
                        });
                    }
                    break;

                case 'unit-cleanings' :

                    if(to.name == 'custom-index'){

                        $(document).arrive('h1.mb-3.text-90.font-normal.text-2xl' , function(){
                            let element = $(this) ;
                            element.text(Nova.app.__('Housekeeping report'))
                        });

                        $(document).arrive("a.btn.btn-default.btn-primary.btn-default.btn-primary", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add Housekeeping Service'));
                        });
                        $(document).arrive("h3.text-base.text-80.font-normal.mb-6", function() {
                            let element = $(this);
                            element.text(Nova.app.__('No Housekeeping Services Added Yet'));
                        });

                        $(document).arrive("a.btn.btn-sm.btn-outline.inline-flex.items-center", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add Housekeeping Service'));
                        });

                        $(document).arrive("div.flex.items-center.ml-auto.px-3" , function(){
                            $(this).remove();
                        });

                        $(document).arrive("ul.breadcrumbs", function() {

                            $('#injectedLiInReports').remove();
                            $('ul.breadcrumbs li:first').after(injectedLiInReports);
                        });


                    }
                    break;

                case 'unit-maintenances' :

                    if(to.name == 'custom-index'){

                        $(document).arrive('h1.mb-3.text-90.font-normal.text-2xl' , function(){
                            let element = $(this) ;
                            element.text(Nova.app.__('Service report under maintenance'))
                        });

                        $(document).arrive("a.btn.btn-default.btn-primary.btn-default.btn-primary", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add Service under maintenance'));
                        });
                        $(document).arrive("h3.text-base.text-80.font-normal.mb-6", function() {
                            let element = $(this);
                            element.text(Nova.app.__('No Service Maintenance Added Yet'));
                        });

                        $(document).arrive("a.btn.btn-sm.btn-outline.inline-flex.items-center", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add Service under maintenance'));
                        });

                        $(document).arrive("div.flex.items-center.ml-auto.px-3" , function(){
                            $(this).remove();
                        });

                        $(document).arrive("ul.breadcrumbs", function() {

                            $('#injectedLiInReports').remove();
                            $('ul.breadcrumbs li:first').after(injectedLiInReports);
                        });

                    }
                    break;

                case 'occupieds' :


                    if(to.name == 'custom-index'){

                        $(document).arrive('h1.mb-3.text-90.font-normal.text-2xl' , function(){
                            let element = $(this) ;
                            element.text(Nova.app.__('Occupieds report'))
                        });


                        $(document).arrive("h3.text-base.text-80.font-normal.mb-6", function() {
                            let element = $(this);
                            element.text(Nova.app.__('No Occupieds Percentage found'));
                        });


                        $(document).arrive("div.flex.items-center.ml-auto.px-3" , function(){
                            $(this).remove();
                        });

                        $(document).arrive("ul.breadcrumbs", function() {

                            $('#injectedLiInReports').remove();
                            $('ul.breadcrumbs li:first').after(injectedLiInReports);
                        });


                    }
                    break;
                case 'reservation-transfers' :
                    if(to.name == 'custom-index'){
                        $(document).arrive('h1.mb-3.text-90.font-normal.text-2xl' , function(){
                            let element = $(this) ;
                            element.text(Nova.app.__('Reservation Transfers'))
                        });
                        $(document).arrive("div.flex.items-center.ml-auto.px-3" , function(){
                            $(this).remove();
                        });

                        $(document).arrive("ul.breadcrumbs", function() {

                            $('#injectedLiInReports').remove();
                            $('ul.breadcrumbs li:first').after(injectedLiInReports);
                        });
                    }
                    break;
                case 'customers':
                    if(to.name == 'custom-index'){


                        // Hiding lenses
                        $(document).arrive("div.dropdown.relative.bg-30", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });


                        $(document).arrive("div[dusk*='filter-selector']", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });

                        $(document).arrive("div[dusk*='select-all-dropdown']", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });

                        $(document).arrive("a.btn.btn-sm.btn-outline.inline-flex.items-center", function() {
                            let element = $(this);

                            element.addClass('w-auto') ;
                        });



                        $(document).arrive("a.btn.btn-default.btn-primary.btn-default.btn-primary", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add New Customer'));
                        });

                        $(document).arrive("a.btn.btn-default.btn-primary.btn-sm.btn-outline", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add New Customer'));
                        });

                        $(document).arrive("a.btn.btn-sm.btn-outline.inline-flex.items-center", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add New Customer'));
                        });
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Customers'));
                        });
                    }else if(to.name == 'custom-create'){


                        $(document).arrive('div.card.overflow-hidden' , function (){
                            $(this).addClass('custom_add_client');
                        });


                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add New Customer'));
                        });

                        /**
                         * Adding required asteric for required fields
                         */
                        $(document).arrive('label[for="name"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }

                        });

                        $(document).arrive('label[for="phone"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }

                        });

                        $(document).arrive('label[for="nationality"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }

                        });

                        $(document).arrive('label[for="id_number"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }

                        });

                    }else if(to.name == 'detail'){

                        $(document).arrive('li.breadcrumbs__item' , function (){
                            $('ul li.breadcrumbs__item a').each(function(){
                                $(this).click(function () {
                                    Nova.app.$router.push('/customers') ;
                                });
                            });
                        });

                        $(document).arrive("h3.text-base.text-80.font-normal.mb-6", function() {
                            let element = $(this);
                            element.text(Nova.app.__('There are no reservations'));
                        });


                         $(document).arrive("h4.text-90.font-normal.text-2xl.flex-no-shrink", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Customer Details'));

                        });


                        $(document).arrive("h1.mb-3.text-90.font-normal.text-2xl", function() {
                            let element = $(this);

                            // element[0].text(Nova.app.__('Reservations'));
                        });


                        $(document).arrive("a.btn.btn-default.btn-primary.btn-default.btn-primary", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add Service'));
                            element.parent().removeClass('ml-auto');
                            element.parent().addClass('custom-ml-auto');

                        });

                        $(document).arrive("button.btn.btn-default.btn-icon.btn-white", function() {
                            let element = $(this);
                            element.removeClass('mr-3');
                            element.addClass('custom-mx-3');
                            element.parent().addClass('custom-flex');
                        });


                    }else if(to.name == 'custom-edit'){
                          $(document).arrive("h1.mb-3.text-90.font-normal.text-2xl", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Edit Customer'));

                        });

                        /**
                         * Adding required asteric for required fields
                         */
                         $(document).arrive('label[for="name"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }

                        });

                        $(document).arrive('label[for="phone"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }

                        });

                        $(document).arrive('label[for="nationality"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }

                        });

                        $(document).arrive('label[for="id_number"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }
                        });
                    }

                    $(document).arrive("button.btn.btn-default.btn-primary.inline-flex.items-center.relative.ml-auto.mr-3", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save & Add Another'));
                    });

                    $(document).arrive("button[type='submit']", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save'));
                    });



                    break;

                case 'services-categories' :

                    // resource no result label
                    $(document).arrive("h3.text-base.text-80.font-normal.mb-6", function() {
                        let element = $(this);
                        element.text(Nova.app.__('No services categories found'));
                    });
                    if(to.name == 'custom-index'){

                        // Hiding lenses
                        $(document).arrive("div.dropdown.relative.bg-30", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });


                        $(document).arrive("div[dusk*='filter-selector']", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });

                        $(document).arrive("div[dusk*='select-all-dropdown']", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });


                        $(document).arrive("a.btn.btn-sm.btn-outline.inline-flex.items-center", function() {
                            let element = $(this);

                            element.addClass('w-auto') ;
                        });

                        $(document).arrive("a.btn.btn-default.btn-primary.btn-default.btn-primary", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add New Service Category'));
                        });
                        $(document).arrive("a.btn.btn-sm.btn-outline.inline-flex.items-center", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add New Service Category'));
                        });
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Services Category'));
                        });
                    }else if(to.name == 'custom-create'){

                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add New Service Category'));
                        });
                    }else if(to.name == 'detail'){

                        $(document).arrive("h4.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Category Details'));
                        });

                        $(document).arrive("a.btn.btn-default.btn-primary.btn-default.btn-primary", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add Service'));

                            element.parent().removeClass('ml-auto');
                            element.parent().addClass('custom-ml-auto');

                        });

                        $(document).arrive("h3.text-base.text-80.font-normal.mb-6", function() {
                            let element = $(this);
                            element.text(Nova.app.__('No services found!'));
                        });

                        $(document).arrive("a.btn.btn-sm.btn-outline.inline-flex.items-center", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add Service'));
                        });

                        $(document).arrive("button.btn.btn-default.btn-icon.btn-white", function() {
                            let element = $(this);
                            element.removeClass('mr-3');
                            element.addClass('custom-mx-3');
                            element.parent().addClass('custom-flex');
                        });


                    }

                    // save , and save & add another buttons
                    $(document).arrive("button.btn.btn-default.btn-primary.inline-flex.items-center.relative.ml-auto.mr-3", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save & Add Another'));
                    });

                    $(document).arrive("button[type='submit']", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save'));
                    });
                    break;

                case 'services' :

                    // the page heading
                    $(document).arrive("h1.mb-3.text-90", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Add New Service'));
                    });



                    // save , and save & add another buttons
                    $(document).arrive("button.btn.btn-default.btn-primary.inline-flex.items-center.relative.ml-auto.mr-3", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save & Add Another'));
                    });

                    $(document).arrive("button[type='submit']", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save'));
                    });

                    break;

                case 'unit-categories':
                    // add button
                    $(document).arrive("a.btn.btn-default.btn-primary", function() {
                        let element = $(this);
                         element.text(Nova.app.__('Add Category'));
                        // element.remove();
                    });

                    $('<span style="color:red;"> *</span>').remove();


                    if(to.name == 'custom-index'){
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            /* element.text(Nova.app.__('Units Category')); */
                            element.remove();
                        });
                    }else if(to.name == 'custom-create'){
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add a new category for units'));
                        });

                        // Add Asterisk to required fields
                        $(document).arrive('label[for="type_id"]' , function(){
                            let element = this;
                            // console.log($(this).parent().find('.required_span'));
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }
                        });

                        $(document).arrive('label[for="translations_name_en"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }
                        });

                        $(document).arrive('label[for="translations_name_ar"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }
                        });
                        $(document).arrive('label[for="sunday_day_price"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }
                        });
                        $(document).arrive('label[for="monday_day_price"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }
                        });
                        $(document).arrive('label[for="tuesday_day_price"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }
                        });
                        $(document).arrive('label[for="wednesday_day_price"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }
                        });
                        $(document).arrive('label[for="thursday_day_price"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }
                        });
                        $(document).arrive('label[for="friday_day_price"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }
                        });
                        $(document).arrive('label[for="saturday_day_price"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }
                        });
                        $(document).arrive('label[for="month_price"]' , function(){
                            let element = this;
                            if(!$(this).parent().find('.required_span').length){
                                $('<span class="required_span" style="color:red;"> *</span>').insertAfter(element);
                            }
                        });

                    }else if(to.name == 'detail'){
                        $(document).arrive("button.btn.btn-default.btn-icon.btn-white", function() {
                            let element = $(this);
                            element.removeClass('mr-3');
                            element.addClass('custom-mx-3');

                            element.parent().addClass('custom-flex');
                        });
                    }else if(to.name == 'custom-edit'){
                        // $(document).arrive("label.form-file-btn.btn.btn-default.btn-primary", function() {
                        //     let element = $(this);
                        //     element.text(Nova.app.__('Add '))
                        // });
                    }
                    // save , and save & add another buttons
                    $(document).arrive("button.btn.btn-default.btn-primary.inline-flex.items-center.relative.ml-auto.mr-3", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save & Add Another'));
                    });

                    $(document).arrive("button[type='submit']", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save'));
                    });

                    /* ------------------------------- Override the breadcrumb ----------------------------- */
                    $(document).arrive("ul.breadcrumbs", function() {
                        $('#injectedLi').remove();
                        $('ul.breadcrumbs li:first').after(injectedLi);
                    });
                    break;

                case 'units':
                // Hiding lenses
                        $(document).arrive("div.dropdown.relative.bg-30", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });


                        $(document).arrive("div[dusk*='filter-selector']", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });

                        $(document).arrive("div[dusk*='select-all-dropdown']", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });


                        $(document).arrive("a.btn.btn-sm.btn-outline.inline-flex.items-center", function() {
                            let element = $(this);

                            element.addClass('w-auto') ;
                        });

                    // resource no result label
                    $(document).arrive("h3.text-base.text-80.font-normal.mb-6", function() {
                        let element = $(this);
                        element.text(Nova.app.__('You have not added any unit'));
                    });
                    if(to.name == 'custom-index'){
                        $(document).arrive("a.btn.btn-default.btn-primary", function() {
                            let element = $(this);
                             element.text(Nova.app.__('Add Unit'));
                            // element.remove();
                        });

                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            /* element.text(Nova.app.__('Units')); */
                            element.remove();
                        });
                    }else if(to.name == 'custom-create'){
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add a new unit'));
                        });

                        $(document).arrive("label.form-file-btn.btn.btn-default.btn-primary", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add '))
                        });

                    }else if(to.name == 'detail'){
                        $(document).arrive("a.btn.btn-default.btn-icon.bg-primary", function() {
                            let element = $(this);

                            element.addClass('custom-mx');
                            element.parent().addClass('custom-flex');
                        });
                    }else if(to.name == 'custom-edit'){

                        $(document).arrive("label.form-file-btn.btn.btn-default.btn-primary", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add Image(s)'))
                        });
                    }

                    // save , and save & add another buttons
                    $(document).arrive("button.btn.btn-default.btn-primary.inline-flex.items-center.relative.ml-auto.mr-3", function() {
                        let element = $(this);
                        // element.text(Nova.app.__('Save & Add Another'));
                    });

                    $(document).arrive("button[type='submit']", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save'));
                    });
                    break;

                case 'unit-general-features':
                    // add button
                    $(document).arrive("a.btn.btn-default.btn-primary", function() {
                        let element = $(this);
                        // element.remove();
                        element.text(Nova.app.__('Add a public feature'));
                    });

                    // resource no result label
                    $(document).arrive("h3.text-base.text-80.font-normal.mb-6", function() {
                        let element = $(this);
                        element.text(Nova.app.__('You have not added any public feature'));
                    });
                    if(to.name == 'custom-index'){
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.remove();
                            // element.text(Nova.app.__('General Features'));
                        });
                    }else if(to.name == 'custom-create'){
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Added a new public feature'));
                        });
                    }

                    // save , and save & add another buttons
                    $(document).arrive("button.btn.btn-default.btn-primary.inline-flex.items-center.relative.ml-auto.mr-3", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save & Add Another'));
                    });

                    $(document).arrive("button[type='submit']", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save'));
                    });

                    /* ------------------------------- Override the breadcrumb ----------------------------- */
                    $(document).arrive("ul.breadcrumbs", function() {
                        $('#injectedLi').remove();
                        $('ul.breadcrumbs li:first').after(injectedLi);
                    });

                    break;

                case 'unit-special-features' :
                    // add button
                    $(document).arrive("a.btn.btn-default.btn-primary", function() {
                        let element = $(this);
                        // element.remove();
                        element.text(Nova.app.__('Add Special feature'));
                    });

                    // resource no result label
                    $(document).arrive("h3.text-base.text-80.font-normal.mb-6", function() {
                        let element = $(this);
                        element.text(Nova.app.__('You have not added any special feature'));
                    });
                    if(to.name == 'custom-index'){
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.remove();
                            // element.text(Nova.app.__('Special Features'));
                        });
                    }else if(to.name == 'custom-create'){
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add New Special feature'));
                        });
                    }else if(to.name == 'custom-edit'){
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Edit Special feature'));
                        });
                    }

                    // save , and save & add another buttons
                    $(document).arrive("button.btn.btn-default.btn-primary.inline-flex.items-center.relative.ml-auto.mr-3", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save & Add Another'));
                    });

                    $(document).arrive("button[type='submit']", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save'));
                    });

                    /* ------------------------------- Override the breadcrumb ----------------------------- */
                    $(document).arrive("ul.breadcrumbs", function() {
                        $('#injectedLi').remove();
                        $('ul.breadcrumbs li:first').after(injectedLi);
                    });
                    break ;

                case 'unit-options' :
                    // add button
                    $(document).arrive("a.btn.btn-default.btn-primary", function() {
                        let element = $(this);
                          element.text(Nova.app.__('Add an option'));
                        // element.remove();
                    });

                    // resource no result label
                    $(document).arrive("h3.text-base.text-80.font-normal.mb-6", function() {
                        let element = $(this);
                        element.text(Nova.app.__('You have not added any option for units'));
                    });

                    if(to.name == 'custom-index'){
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            /* element.text(Nova.app.__('Units Options')); */
                            element.remove();
                        });
                    }else if(to.name == 'custom-create'){
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add a new option'));
                        });
                    }
                    // save , and save & add another buttons
                    $(document).arrive("button.btn.btn-default.btn-primary.inline-flex.items-center.relative.ml-auto.mr-3", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save & Add Another'));
                    });

                    $(document).arrive("button[type='submit']", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save'));
                    });


                    /* ------------------------------- Override the breadcrumb ----------------------------- */
                    $(document).arrive("ul.breadcrumbs", function() {
                        $('#injectedLi').remove();
                        $('ul.breadcrumbs li:first').after(injectedLi);
                    });
                    break;

                case 'users' :
                // Hiding lenses
                        $(document).arrive("div.dropdown.relative.bg-30", function() {
                            let element = $(this);
                            // console.log(element);
                            // element.hide();
                        });


                        $(document).arrive("div[dusk*='filter-selector']", function() {
                            let element = $(this);
                            // console.log(element);
                            // element.hide();
                        });

                        $(document).arrive("div[dusk*='select-all-dropdown']", function() {
                            let element = $(this);
                            // console.log(element);
                            // element.hide();
                        });


                        $(document).arrive("a.btn.btn-sm.btn-outline.inline-flex.items-center", function() {
                            let element = $(this);

                            element.addClass('w-auto') ;
                        });

                    // resource no result label
                    $(document).arrive("h3.text-base.text-80.font-normal.mb-6", function() {
                        let element = $(this);
                        element.text(Nova.app.__('There are no users added yet'));
                    });
                    if(to.name == 'custom-index'){
                        $(document).arrive("a.btn.btn-default.btn-primary.btn-default.btn-primary", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add a new user'));
                        });
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Users'));
                        });
                    }else if(to.name == 'custom-create'){

                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add a new user'));
                        });
                    }else if(to.name == 'detail'){

                        // $(document).arrive("img", function() {
                        //     let element = $(this);
                        //     element.addClass('custom-profile-img');
                        //
                        // });


                        $(document).arrive("h4.text-90.font-normal.text-2xl.flex-no-shrink", function() {
                            let element = $(this);
                            element.text(Nova.app.__('User Information'));

                        });

                        $(document).arrive("a.btn.btn-default.btn-primary.btn-default.btn-primary", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Attach Role'));
                            element.parent().removeClass('ml-auto');
                            element.parent().addClass('custom-ml-auto');
                        });

                        $(document).arrive("input[type='search']", function() {
                            let element = $(this);
                            element.hide() ;
                            element.prev().hide();
                        });


                        // resource no result label
                        $(document).arrive("h3.text-base.text-80.font-normal.mb-6", function() {
                            let element = $(this);
                            element.text(Nova.app.__('No role has been attached to this user'));
                        });

                        $(document).arrive("a.btn.btn-default.btn-primary.btn-sm.btn-outline", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Attach Role'));

                        });

                        $(document).arrive("a.btn.btn-default.btn-icon.bg-primary", function() {
                            let element = $(this);
                            element.removeClass('mr-3');
                            element.addClass('custom-mx-3');


                            element.parent().addClass('custom-flex');
                        });


                    }else if(to.name == 'custom-attach'){
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Attach New Role'));
                        });
                    }

                    // save , and save & add another buttons
                    $(document).arrive("button.btn.btn-default.btn-primary.inline-flex.items-center.relative.ml-auto.mr-3", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save & Continue Update Profile'));
                    });

                    $(document).arrive("button[type='submit']", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save'));
                    });

                    break;

                case 'terms' :
                    // resource no result label
                    $(document).arrive("h3.text-base.text-80.font-normal.mb-6", function() {
                        let element = $(this);
                        element.text(Nova.app.__('No Terms has been added yet'));
                    });
                    if(to.name == 'custom-index'){
                        $(document).arrive("a.btn.btn-default.btn-primary.btn-default.btn-primary", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add new Term'));
                        });
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Management Of Withdraw & Deposit Terms'));
                        });
                    }else if(to.name == 'custom-create'){

                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add new Term'));
                        });
                    }else if(to.name == 'detail'){

                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Term Details'));
                        });

                        $(document).arrive("button.btn.btn-default.btn-icon.btn-white", function() {
                            let element = $(this);
                            element.removeClass('mr-3');
                            element.addClass('custom-mx-3');
                            element.parent().addClass('custom-flex');
                        });


                    }

                    // save , and save & add another buttons
                    $(document).arrive("button.btn.btn-default.btn-primary.inline-flex.items-center.relative.ml-auto.mr-3", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save & Add Another'));
                    });

                    $(document).arrive("button[type='submit']", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save'));
                    });
                    break;
                case 'sources' :
                // Hiding lenses
                        $(document).arrive("div.dropdown.relative.bg-30", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });


                        $(document).arrive("div[dusk*='filter-selector']", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });

                        $(document).arrive("div[dusk*='select-all-dropdown']", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });


                        $(document).arrive("a.btn.btn-sm.btn-outline.inline-flex.items-center", function() {
                            let element = $(this);

                            element.addClass('w-auto') ;
                        });
                    // resource no result label
                    $(document).arrive("h3.text-base.text-80.font-normal.mb-6", function() {
                        let element = $(this);
                        element.text(Nova.app.__('No Sources has been added yet'));
                    });
                    if(to.name == 'custom-index'){
                        $(document).arrive("a.btn.btn-default.btn-primary.btn-default.btn-primary", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add new Source'));
                        });
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Management Of Sources'));
                        });
                    }else if(to.name == 'custom-create'){

                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add new Source'));
                        });
                    }else if(to.name == 'detail'){

                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Source Details'));
                        });

                        $(document).arrive("button.btn.btn-default.btn-icon.btn-white", function() {
                            let element = $(this);
                            element.removeClass('mr-3');
                            element.addClass('custom-mx-3');
                            element.parent().addClass('custom-flex');
                        });


                    }

                    // save , and save & add another buttons
                    $(document).arrive("button.btn.btn-default.btn-primary.inline-flex.items-center.relative.ml-auto.mr-3", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save & Add Another'));
                    });

                    $(document).arrive("button[type='submit']", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save'));
                    });
                    break;

                case 'roles' :// Hiding lenses

                        $(document).arrive("div.dropdown.relative.bg-30", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });


                        $(document).arrive("div[dusk*='roles-detail-component']", function() {
                            let element = $(this);
                            element.find('span.w-full.flex').addClass('rolesItems');
                        });

                        $(document).arrive("div.w-full.flex.flex-wrap", function() {
                            let element = $(this);
                            element.addClass('rolesItems');
                        });



                        $(document).arrive("div[dusk*='filter-selector']", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });

                        $(document).arrive("div[dusk*='select-all-dropdown']", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });


                        $(document).arrive("a.btn.btn-sm.btn-outline.inline-flex.items-center", function() {
                            let element = $(this);

                            element.addClass('w-auto') ;
                        });
                    // add button
                    $(document).arrive("a.btn.btn-default.btn-primary", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Add a new role'));
                    });

                    // resource no result label
                    $(document).arrive("h3.text-base.text-80.font-normal.mb-6", function() {
                        let element = $(this);
                        element.text(Nova.app.__('No roles has been added yet'));
                    });
                    if(to.name == 'custom-index'){
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Roles & permissions'));
                        });
                    }else if(to.name == 'custom-create'){
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Add a new role'));
                        });
                    }else if(to.name == 'custom-edit'){
                        // the page heading
                        $(document).arrive("h1.mb-3.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Edit Role & Permissions'));
                            var editRolesUl = element.parent().parent().find('div.mb-3').find('div').find('nav').find('ul');
                            editRolesUl.find('li').each(function(i){
                                 $(this).find('a[href="/home/resources/roles"]').text(Nova.app.__('Roles & permissions'))
                                 $(this).find('a[href="/home/resources/roles/' + to.params.resourceId + '"]').text(to.params.resourceId)
                            });
                        });
                    }else if(to.name == 'detail'){
                        // the page heading
                        $(document).arrive("h4.text-90", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Role Details'));
                            var detailsRolesUl = $("div[dusk*='roles-detail-component']").parent().find('div.mb-3').find('nav').find('ul');
                            detailsRolesUl.find('li').each(function(i){
                                 $(this).find('a[href="/home/resources/roles"]').text(Nova.app.__('Roles & permissions'))
                                 $(this).find('a[href="/home/resources/roles/' + to.params.resourceId + '"]').text(to.params.resourceId)
                            });
                        });
                    }


                    // save , and save & add another buttons
                    $(document).arrive("button.btn.btn-default.btn-primary.inline-flex.items-center.relative.ml-auto.mr-3", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save & Add Another'));
                    });

                    $(document).arrive("button[type='submit']", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Save'));
                    });
                    break;

                case 'activity-logs' :
                // Hiding lenses
                        $(document).arrive("div.dropdown.relative.bg-30", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });


                        $(document).arrive("div[dusk*='filter-selector']", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });

                        $(document).arrive("div[dusk*='select-all-dropdown']", function() {
                            let element = $(this);
                            // console.log(element);
                            element.hide();
                        });


                        $(document).arrive("a.btn.btn-sm.btn-outline.inline-flex.items-center", function() {
                            let element = $(this);

                            element.addClass('w-auto') ;
                        });

                    if(to.name == 'custom-index'){

                        // the page heading
                        $(document).arrive("h1.mb-3.text-90.font-normal.text-2xl", function() {
                            let element = $(this);
                            element.text(Nova.app.__('Activity Logs'));
                        });
                    }
                    break;

                default:
                    // alert('Nobody Wins!');
            }
        }else{
            const name = router.app._route.name ;

            switch (name){
                case  'customers' :


                    $(document).arrive("a.btn.btn-default.btn-primary", function() {
                        let element = $(this);
                        element.text(Nova.app.__('Add New Customer'));
                    });

                    $(document).arrive("div.no_data_show span", function() {
                        let element = $(this);
                        // console.log(element);
                        element.text(Nova.app.__('No Customers Found!'));
                        element.next().remove();
                    });


                break;
                case 'settings.notifications' :

                      $(document).arrive('div.flex.border-b.border-40.w-full' , function (){
                            $(this).addClass('notipage');
                        });
                    break;

                     case 'settings.general' :
                        // Nova.app.currentTeam.last_subscription && Nova.app.currentTeam.last_subscription.stripe_plan == 'monthly-yearly'
                        // console.log(Nova.app.currentTeam.last_subscription)


                      $(document).arrive('div.custom_inputs' , function (){
                            if(Nova.app.currentTeam.last_subscription && Nova.app.currentTeam.last_subscription.stripe_plan == 'monthly-yearly'){
                                $("input#tax").parent().parent().parent().parent().addClass('d-none');
                                $("input#accommodation_tax").parent().parent().parent().parent().addClass('d-none');
                                $("input#tourism_tax").parent().parent().parent().parent().addClass('d-none');

                                // $('label[for="automatic_renewal_of_reservations"]').parent().parent().addClass('d-none');
                                // $('label[for="daily_single_days"]').parent().parent().addClass('d-none');
                                // $('label[for="monthly_single_days"]').parent().parent().addClass('d-none');
                            }


                        });
                    break;
            }

        }
    })
});




