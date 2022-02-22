/**
 * MAIN METHOD
 *
 */

// ITEM DATA BODY EVENTS
ItemDataBody.prototype.finishDelegates = function() {
    var itemDataBody = this;
	var system       = itemDataBody.itemData.pane.pane.formWizardTabPane.navItem.tab.sidebarMenuItem.sidebarMenu.sidebar.system;

    // ItemDataBody :: Delegate CLICK :: span.btn-add-follower
    itemDataBody.el.delegate('span.btn-add-follower', 'click', function() {
        // cache elements
        var parentForm = itemDataBody.el.find($(this).attr('data-ref'));
        var formBreak    = parentForm.find('>.form-group-break.hidden');
        var formAttached = parentForm.find('>.form-group-attached.hidden');
        var formIndex    = parentForm.find('>.form-group-attached').length.toString();

        // create follower form
        var h = "";
        h += "<div class='form form-group-break'>";
        h += formBreak.html();
        h += "</div>";
        h += "<div class='form form-group-attached'>";
        h += formAttached.html();
        h += "</div>";
        var form = $("<div>" + h + "</div>");

        // modify form count
        form.find('.n-follower').text(formIndex);

        // modify form href and ref attributes
        var inputGroup = form.find('.input-group-search[data-model="PhCitizen"]');
        inputGroup.attr('data-href', '#srch-follower-new-' + formIndex);
        inputGroup.attr('data-ref' , '#srch-follower-new-'  + formIndex);

        // append form to parentForm and copy the new input values
        parentForm.append(form.html());
        form = parentForm.find('>.form-group-attached:last-child');
        form.find('.input-group-search[data-model="PhCitizen"] input').val(formAttached.find('.input-group-search[data-model="PhCitizen"] input').val());

        // render input objects in parentForm
        itemDataBody.renderInputObjects(parentForm);

        // click search button
        setTimeout(function() {
            var appended = parentForm.find('.input-group-search[data-model="PhCitizen"][data-href="#srch-follower-new-' + formIndex + '"]');
            if(appended.length > 0)
                appended.find('button').click();

        }, 1);
    });

    // ItemDataBody :: Delegate CLICK :: span.btn-remove-location
    itemDataBody.el.delegate('span.btn-remove-follower', 'click', function() {
        // cache elements
        var formBreak     = $(this).parent().parent().parent().parent().parent();
        var formLocations = formBreak.next();
        var parentForm    = formBreak.parent();

        // remove elements
        formLocations.fadeOut(511, function() {
            formLocations.remove();
        });
        formBreak.fadeOut(512, function() {
            formBreak.remove();

            // fix .n-location labels
            var i = 0;
            parentForm.find('.n-follower').each(function() {
                var n = parseInt($(this).text());
                if(n > 0) {
                    i += 1;
                    $(this).text(i.toString());
                }
            });
        });
    });

    // ItemDataBody :: Delegate CLICK :: span.btn-clear-leader
    itemDataBody.el.delegate('span.btn-clear-leader', 'click', function() {
        var inputGroupSearch = itemDataBody.el.find('.input-group-search[data-href="#srch-leader"]');
        inputGroupSearch.attr('data-key', '0');
        inputGroupSearch.find('.img-avatar').attr('src', system.root + 'img/citizens/___.jpg');
        inputGroupSearch.find('input#srch-leader').val('');
    });

    // ItemDataBody :: Delegate FOCUS :: input.txt-lastname
    itemDataBody.el.delegate('input#txt-lastname', 'click', function() {
        var v = $(this).val().trim();
        if(v.length > 2) {
            if(v.substr(0, 2) === '@[') {
                $(this).select();
                $(this).focus();
            }
        }
    });

    // ItemDataBody :: Delegate CLICK :: button.btn-print-list
    itemDataBody.el.delegate('button.btn-print-list', 'click', function() {
        var btnPrintList = $(this);
        var leaderID = btnPrintList.attr('data-id');
        var icon     = btnPrintList.find('.fa');

        btnPrintList.attr('disabled', true);
        btnPrintList.prop('disabled', true);
        icon.removeClass('fa-list');
        icon.addClass('fa-spinner');
        icon.addClass('fa-pulse');

        window.open(system.root + 'php/models/Leader.php?print&id=' + leaderID + '&form=', 'iF');
        var iFrame = $('#iF');
        iFrame.on('load', function() {
            btnPrintList.attr('disabled', false);
            btnPrintList.prop('disabled', false);
            icon.addClass('fa-list');
            icon.removeClass('fa-spinner');
            icon.removeClass('fa-pulse');
            iFrame.off();
        });
    });
};


$(function() {
    var system = new System($('body'));
    system.sidebar.menu.activateCurrentMenuItem();

    window.onresize = function() {
        system.positionElements({});
    };

    // sidebar menu item prevent default
    system.el.find('.sidebar-menu-item > a').on('click', function(e) {
        e.preventDefault();
    });

    // EVENT: BROWSER BACK OR FORWARD BUTTON
    window.onpopstate = function(event) {
        var page = 'home';
        var tab  =  1;
        var item = -1;
        var location = document.location.toString().split('?');
        if(location.length > 1) {
            var parameters = location[1].split('=');
            var pages      = parameters[0].split('-');
            if(pages.length > 1) {
                page = pages[0];
                tab  = parseInt(pages[1]);
                if(pages.length > 2) {
                    item = parseInt(pages[2]);
                }
            }
        }

        var sidebarMenuItem = system.sidebar.menu.searchMenuItem('?'+page);
        if(sidebarMenuItem) {
            // deactivate previous tab
            var activeTab = sidebarMenuItem.getActiveTab();
            if(activeTab)
                activeTab.isActive = false;

            // activate new tab
            activeTab = sidebarMenuItem.searchTab(tab);
            if(activeTab) {
                activeTab.isActive   = true;
                activeTab.activeItem = item;
            }

            // activate the sidebarMenuItem
            sidebarMenuItem.activate({isMenuClicked: false});
        }
        else
            window.location.reload();
    };

    // EVENT: USER LOGOUT
    $('#btnShowLogoutPrompt').on('click', function() {
        system.confirmDialog.show("CONFIRM LOGOUT", "<span style='font-size: 1.2em'>Do you really want to logout?</span>", function() {
            Pace.restart();
            $.ajax({
                type: 'POST',
                url: system.index + 'php/ajax/login.php',
                data: {
                    logout_user: 1
                },
                success: function(data) {
                    var respose = JSON.parse(data);
                    if(respose.success == "1") {
                        system.confirmDialog.hide(function() {
                            system.el.fadeOut();
                            window.open(system.index + 'admin/index.php?logout', '_self');
                        });
                    }
                    Pace.stop();
                },
                error: function(data) {
                    system.confirmDialog.hide(function() {
                        Pace.stop();
                        system.messageDialog.show(
                            '<span class="text-danger">ERROR ' + data.status + ' ' + '(' + (data.status == 0 ? 'NO CONNECTION' : data.statusText) + ')' + '</span>',
                            '<b>UNABLE TO LOGOUT FROM </b>' + '<span class="text-primary">' + model + '</span>',
                            function () {
                                window.location.reload();
                            }
                        );
                    });
                }
            });
        });
    });

    // EVENT: DATA SCAN
    $('a.data-scan').on('click', function(e) {
        e.preventDefault();
        system.confirmDialog.show("<span class='text-success'>DATA SCAN</span>", "<big class='text-danger'>Performing a data scan may take long and slow down other system processes.</big><br><big>Do you want to proceed?</big>", function() {
            system.confirmDialog.hide();
            window.open('scan', '_blank');
        });
    });
});


// GENERATE DASHBOARD
System.prototype.generateDashboard = function() {
    var h = "";
    h += "<div class='row'>";

    	// BARANGAYS
    	h += "<div class='col-md-4 mb-3'>";
            h += "<div class='widget-9 card no-border bg-complete-dark no-margin widget-loader-bar'>";
                h += "<div class='container-xs-height full-height'>";
                    h += "<div class='card-header'>";
                        h += "<div class='card-title text-white'>";
                            h += "<span class='font-montserrat fs-11 all-caps'>BARANGAYS <i class='fa fa-chevron-right'></i></span>";
                        h += "</div>";
                        h += "<div class='card-controls'>";
                            h += "<ul>";
                                 h += "<li>";
                                    h += "<a href='#' class='card-refresh text-black' data-toggle='refresh'><i class='card-icon card-icon-refresh'></i></a>";
                                h += "</li>";
                            h += "</ul>";
                        h += "</div>";
                    h += "</div>";
                    h += "<div class='p-l-20'>";
                        h += "<h1 class='no-margin p-b-5 text-white dashboard-barangays'><span class='loading'></span></h1>";
                    h += "</div>";
                h += "</div>";
            h += "</div>";
        h += "</div>";


    	// CITIZENS
    	h += "<div class='col-md-4 mb-3'>";
            h += "<div class='widget-9 card no-border bg-success no-margin widget-loader-bar'>";
                h += "<div class='container-xs-height full-height'>";
                    h += "<div class='card-header'>";
                        h += "<div class='card-title text-white'>";
                            h += "<span class='font-montserrat fs-11 all-caps'>CITIZENS <i class='fa fa-chevron-right'></i></span>";
                        h += "</div>";
                        h += "<div class='card-controls'>";
                            h += "<ul>";
                                 h += "<li>";
                                    h += "<a href='#' class='card-refresh text-black' data-toggle='refresh'><i class='card-icon card-icon-refresh'></i></a>";
                                h += "</li>";
                            h += "</ul>";
                        h += "</div>";
                    h += "</div>";
                    h += "<div class='p-l-20'>";
                        h += "<h1 class='no-margin p-b-5 text-white dashboard-citizens'><span class='loading'></span></h1>";
                        h += "<span class='small hint-text text-white no-margin'>REGISTERED : <b class='dashboard-registered-citizens'></b></span>";
                        h += "<span class='small text-white mx-2'>|</span>";
                        h += "<span class='small hint-text text-white no-margin'>UNREGISTERED : <b class='dashboard-unregistered-citizens'></b></span>";
                    h += "</div>";
                h += "</div>";
            h += "</div>";
        h += "</div>";

    	// LEADERS
    	h += "<div class='col-md-4 mb-3'>";
            h += "<div class='widget-9 card no-border bg-danger-dark no-margin widget-loader-bar'>";
                h += "<div class='container-xs-height full-height'>";
                    h += "<div class='card-header'>";
                        h += "<div class='card-title text-white'>";
                            h += "<span class='font-montserrat fs-11 all-caps'>LEADERS <i class='fa fa-chevron-right'></i></span>";
                        h += "</div>";
                        h += "<div class='card-controls'>";
                            h += "<ul>";
                                 h += "<li>";
                                    h += "<a href='#' class='card-refresh text-black' data-toggle='refresh'><i class='card-icon card-icon-refresh'></i></a>";
                                h += "</li>";
                            h += "</ul>";
                        h += "</div>";
                    h += "</div>";
                    h += "<div class='p-l-20'>";
                        h += "<h1 class='no-margin p-b-5 text-white dashboard-leaders'><span class='loading'></span></h1>";
                        h += "<span class='small hint-text text-white no-margin'>BRGY : <b class='dashboard-barangay-leaders'></b></span>";
                        h += "<span class='small text-white mx-2'>|</span>";
                        h += "<span class='small hint-text text-white no-margin'>MUN : <b class='dashboard-employee-leaders'></b></span>";
                        h += "<span class='small text-white mx-2'>|</span>";
                        h += "<span class='small hint-text text-white no-margin'>BOTH : <b class='dashboard-both-leaders'></b></span>";
                    h += "</div>";
                h += "</div>";
            h += "</div>";
        h += "</div>";
    h += "</div>"

    h += "<div class='d-none d-md-block'>";
        h += "<div class='dashboard-chart' id='dashboard-chart'></div>"
    h += "</div>";

    h += "<hr class='dashboard-hr d-none d-md-block hidden'>";

    h += "<div class='row'>";
        h += "<div class='col-md-12 dashboard-additional bg-white'></div>";
    h += "</div>";

    return h;
};

// UPDATE DASHBOARD
System.prototype.updateDashboard = function(model) {
    var system = this;

    Pace.restart();
    $.ajax({
        type: 'POST',
        url: system.root + system.models + model + '.php',
        data: {

        },
        success: function(data) {
            var formWizardBody = system.content.body.contentFormWizard.body;
            var tabPane        = formWizardBody.getActiveTabPane();
            var activeNavItem  = formWizardBody.formWizard.header.getActiveNavItem();

            if(tabPane && activeNavItem) {
                if(activeNavItem.tab.model === model) {
                    var response = JSON.parse(data);
                    if(response.error !== '') {
                        system.messageDialog.show(response.error, '', function() {
                            window.location.reload();
                        })
                    }
                    else {
                        if(response.success.message !== '')
                            system.messageDialog.show(response.success.message, response.success.sub_message);
                        else {
                            data = response.success.data;

                            tabPane.el.find('.dashboard-hr').removeClass('hidden');
                            tabPane.el.find('.dashboard-barangays').html(system.parseCurrency(data.total_barangays, true));
                            tabPane.el.find('.dashboard-citizens').html(system.parseCurrency(data.total_citizens, true));
                            tabPane.el.find('.dashboard-registered-citizens').html(system.parseCurrency((data.total_citizens - data.unregistered_citizens_total), true));
                            tabPane.el.find('.dashboard-unregistered-citizens').html(system.parseCurrency(data.unregistered_citizens_total, true));
                            tabPane.el.find('.dashboard-leaders').html(system.parseCurrency(data.total_leaders, true));
                            tabPane.el.find('.dashboard-barangay-leaders').html(system.parseCurrency(data.total_leaders_from_barangay, true));
                            tabPane.el.find('.dashboard-employee-leaders').html(system.parseCurrency(data.total_leaders_from_employees, true));
                            tabPane.el.find('.dashboard-both-leaders').html(system.parseCurrency(data.total_leaders_from_both, true));


                            // Initialize chart data
                            var chart = {
                                chart: {
                                    type: 'column'
                                },
                                title: {
                                    text: ''
                                },
                                xAxis: {
                                    categories: [],
                                    crosshair : true
                                },
                                yAxis: {
                                    min: 0,
                                    title: {
                                        text: 'Total Voters'
                                    }
                                },
                                tooltip: {
                                    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                                    pointFormat : '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' + '<td style="padding:0"><b>{point.y:.0f}</b></td></tr>',
                                    footerFormat: '</table>',
                                    shared: true,
                                    useHTML: true
                                },
                                plotOptions: {
                                    column: {
                                        pointPadding: 0.2,
                                        borderWidth: 0
                                    }
                                },
                                credits: {
                                    enabled: false
                                },
                                series: [
                                    {
                                        name : 'Citizens',
                                        data : [],
                                        color: '#47b5ab'

                                    },
                                    {
                                        name : 'Unregistered',
                                        data : [],
                                        color: '#444444'

                                    },
                                    {
                                        name: 'Leaders',
                                        data : [],
                                        color: '#be5d5e'

                                    }
                                ]
                            };

                            var h = "";
                            h += "<div class='row d-md-none d-block'>";
                                h += "<div class='col-12'>";
                                    h += "<table class='table table-condensed table-sm table-hover table-bordered text-montserrat mb-3'>";
                                        h += "<thead>";
                                            h += "<tr>";
                                                h += "<th class='bg-complete-lighter'><h6 class='no-margin'><b>Barangay</b></h6></th>";
                                                h += "<th class='bg-complete-lighter text-center'><h6 class='no-margin'><b>Citizens</b></h6></th>";
                                                h += "<th class='bg-complete-lighter text-center'><h6 class='no-margin'><b>Leaders</b></h6></th>";
                                            h += "</tr>";
                                        h += "</thead>";
                                        h += "<tbody>";
                                            for(var i=0; i<data.barangays.length; i++) {
                                                var barangay = data.barangays[i];
                                                if(i === 0) {
                                                    h += "<tr class='vertical-middle'>";
                                                        h += "<td><p class='no-margin'>(UNKNOWN BRGY.)</p></td>";
                                                        h += "<td align='center'>";
                                                            h += "<h6 class='no-margin' title='Citizens'><b>" + system.parseCurrency(data.no_barangay_citizens.length, true) + "</b></h6>";
                                                            if(data.no_barangay_unregistered_citizens > 0)
                                                                h += "<small class='text-danger'>UNREGISTERED: <b>" + system.parseCurrency(data.no_barangay_unregistered_citizens, true) + "</b></small>";
                                                        h += "</td>";
                                                        h += "<td align='center'>";
                                                            h += "<h6 class='no-margin' title='Leaders'><b>" + system.parseCurrency(data.no_barangay_leaders, true) + "</b></h6>";
                                                            if(data.no_barangay_unregistered_citizens > 0)
                                                                h += "<small class='text-danger'><b>&nbsp;</b></small>";
                                                        h += "</td>";
                                                    h += "</tr>";
                                                }
                                                h += "<tr class='vertical-middle'>";
                                                    h += "<td><p class='no-margin'>" + (i + 1).toString() + ". <b>" + barangay.name + "</b></p></td>";
                                                    h += "<td align='center'>";
                                                        h += "<h6 class='no-margin' title='Citizens'><a href='dashboard.php?reports-1-" + barangay.id + "' target='_blank' class='text-master'><b>" + system.parseCurrency(barangay.citizens, true) + "</b></a></h6>";
                                                        if(barangay.unregistered_citizens > 0)
                                                            h += "<small class='text-danger'>UNREGISTERED: <b>" + system.parseCurrency(barangay.unregistered_citizens, true) + "</b></small>";
                                                    h += "</td>";
                                                    h += "<td align='center'>";
                                                        h += "<h6 class='no-margin' title='Leaders'><a href='dashboard.php?reports-2-" + barangay.id + "' target='_blank' class='text-master'><b>" + system.parseCurrency(barangay.leaders, true) + "</b></a></h6>";
                                                        if(barangay.unregistered_citizens > 0)
                                                            h += "<small class='text-danger'><b>&nbsp;</b></small>";
                                                    h += "</td>";

                                                    // add chart data
                                                    chart.xAxis.categories.push(barangay.name);
                                                    chart.series[0].data.push(barangay.citizens);
                                                    chart.series[1].data.push(barangay.unregistered_citizens);
                                                    chart.series[2].data.push(barangay.leaders);
                                                h += "</tr>";
                                            }
                                        h += "</tbody>";
                                    h += "</table>";
                                h += "</div>";
                            h += "</div>";
                            h += "<div class='row'>";

                                // NO BARANGAY
                                h += "<div class='col-md-3 text-montserrat'>";
                                    h += "<table class='table table-condensed table-hover table-bordered text-montserrat mb-3'>";
                                        h += "<thead>";
                                            h += "<tr>";
                                            h += "<th class='bg-primary-lighter'><a href='dashboard.php?reports-3-1' target='_blank'><h6 class='no-margin'><b class='text-primary'>No Barangay" + (data.no_barangay_citizens_total > 0 ? " <b>(" + system.parseCurrency(data.no_barangay_citizens_total, true) + ")</b>" : "") + "</b></h6></a></th>";
                                            h += "</tr>";
                                        h += "</thead>";
                                        h += "<tbody>";
                                            if(data.no_barangay_citizens_total <= 0) {
                                                h += "<tr>";
                                                    h += "<td>(NONE)</td>";
                                                h += "</tr>";
                                            }
                                            else {
                                                for(var x=0; x<data.no_barangay_citizens.length; x++) {
                                                    var noBarangayCitizen = data.no_barangay_citizens[x];
                                                    h += "<tr>";
                                                        h += "<td>";
                                                            h += "<span class='monospace'>" + (x + 1).toString() + ".</span> ";
                                                            h += "<b><a href='dashboard.php?citizens-1-" + noBarangayCitizen.id + "' target='_blank' class='text-primary'>" + noBarangayCitizen.name + "</a></b>";
                                                        h += "</td>";
                                                    h += "</tr>";
                                                    if(x >= 99) {
                                                        h += "<tr>";
                                                            h += "<td align='center'>";
                                                                h += "<h6 class='no-margin'><a href='dashboard.php?reports-3-1' target='_blank' class='text-primary'><span class='fa fa-fw fa-external-link-alt'></span> <b>VIEW ALL</b></a></h6>";
                                                            h += "</td>";
                                                        h += "</tr>";
                                                    }
                                                }
                                            }
                                        h += "</tbody>";
                                    h += "</table>";
                                h += "</div>";


                                // NO LEADER
                                h += "<div class='col-md-3 text-montserrat'>";
                                    h += "<table class='table table-condensed table-hover table-bordered text-montserrat mb-3'>";
                                        h += "<thead>";
                                            h += "<tr>";
                                            h += "<th style='background: #e9ebed'><a href='dashboard.php?reports-3-2' target='_blank'><h6 class='no-margin'><b class='text-info'>NO LEADER" + (data.no_leader_citizens_total > 0 ? " <b>(" + system.parseCurrency(data.no_leader_citizens_total, true) + ")</b>" : "") + "</b></h6></a></th>";
                                            h += "</tr>";
                                        h += "</thead>";
                                        h += "<tbody>";
                                            if(data.no_leader_citizens_total <= 0) {
                                                h += "<tr>";
                                                    h += "<td>(NONE)</td>";
                                                h += "</tr>";
                                            }
                                            else {
                                                for(var x=0; x<data.no_leader_citizens.length; x++) {
                                                    var noLeaderCitizen = data.no_leader_citizens[x];
                                                    h += "<tr>";
                                                        h += "<td>";
                                                            h += "<span class='monospace'>" + (x + 1).toString() + ".</span> ";
                                                            h += "<b><a href='dashboard.php?citizens-1-" + noLeaderCitizen.id + "' target='_blank' style='color: #5b7094'>" + noLeaderCitizen.name + "</a></b>";
                                                        h += "</td>";
                                                    h += "</tr>";
                                                    if(x >= 99) {
                                                        h += "<tr>";
                                                            h += "<td align='center'>";
                                                                h += "<h6 class='no-margin'><a href='dashboard.php?reports-3-2' target='_blank' style='color: #5b7094'><span class='fa fa-fw fa-external-link-alt'></span> <b>VIEW ALL</b></a></h6>";
                                                            h += "</td>";
                                                        h += "</tr>";
                                                    }
                                                }
                                            }
                                        h += "</tbody>";
                                    h += "</table>";
                                h+= "</div>";


                                // DEFAULT LEADERS
                                h += "<div class='col-md-3 text-montserrat'>";
                                    h += "<table class='table table-condensed table-hover table-bordered text-montserrat mb-3'>";
                                        h += "<thead>";
                                            h += "<tr>";
                                            h += "<th style='background: #fceebb'><a href='dashboard.php?reports-3-3' target='_blank'><h6 class='no-margin'><b style='color: #655a17'>DEFAULT LEADERS" + (data.default_leaders_total > 0 ? " <b>(" + system.parseCurrency(data.default_leaders_total, true) + ")</b>" : "") + "</b></h6></a></th>";
                                            h += "</tr>";
                                        h += "</thead>";
                                        h += "<tbody>";
                                            if(data.default_leaders_total <= 0) {
                                                h += "<tr>";
                                                    h += "<td>(NONE)</td>";
                                                h += "</tr>";
                                            }
                                            else {
                                                for(var x=0; x<data.default_leaders.length; x++) {
                                                    var defaultLeader = data.default_leaders[x];
                                                    h += "<tr>";
                                                        h += "<td>";
                                                            h += "<span class='monospace'>" + (x + 1).toString() + ".</span> ";
                                                            h += "<b><a href='dashboard.php?leaders-1-" + defaultLeader.id + "' target='_blank' style='color: #b59b4c'>" + defaultLeader.name + "</a></b>";
                                                        h += "</td>";
                                                    h += "</tr>";
                                                    if(x >= 99) {
                                                        h += "<tr>";
                                                            h += "<td align='center'>";
                                                                h += "<h6 class='no-margin'><a href='dashboard.php?reports-3-3' target='_blank' style='color: #b59b4c'><span class='fa fa-fw fa-external-link-alt'></span> <b>VIEW ALL</b></a></h6>";
                                                            h += "</td>";
                                                        h += "</tr>";
                                                    }
                                                }
                                            }
                                        h += "</tbody>";
                                    h += "</table>";
                                h+= "</div>";

                                // UNREGISTERED CITIZENS
                                h += "<div class='col-md-3 text-montserrat'>";
                                    h += "<table class='table table-condensed table-hover table-bordered text-montserrat mb-3'>";
                                        h += "<thead>";
                                            h += "<tr>";
                                            h += "<th class='bg-danger-lighter'><a href='dashboard.php?reports-3-4' target='_blank'><h6 class='no-margin'><b style='color: #7d3132'>Unregistered" + (data.unregistered_citizens_total > 0 ? " <b>(" + system.parseCurrency(data.unregistered_citizens_total, true) + ")</b>" : "") + "</b></h6></a></th>";
                                            h += "</tr>";
                                        h += "</thead>";
                                        h += "<tbody>";
                                            if(data.unregistered_citizens_total <= 0) {
                                                h += "<tr>";
                                                    h += "<td>(NONE)</td>";
                                                h += "</tr>";
                                            }
                                            else {
                                                for(var x=0; x<data.unregistered_citizens.length; x++) {
                                                    var unregisteredCitizen = data.unregistered_citizens[x];
                                                    h += "<tr>";
                                                        h += "<td>";
                                                            h += "<span class='monospace'>" + (x + 1).toString() + ".</span> ";
                                                            h += "<b><a href='dashboard.php?citizens-1-" + unregisteredCitizen.id + "' target='_blank' class='text-danger'>" + unregisteredCitizen.name + "</a></b>";
                                                        h += "</td>";
                                                    h += "</tr>";
                                                    if(x >= 99) {
                                                        h += "<tr>";
                                                            h += "<td align='center'>";
                                                               h += "<h6 class='no-margin'><a href='dashboard.php?reports-3-4' target='_blank' class='text-danger'><span class='fa fa-fw fa-external-link-alt'></span> <b>VIEW ALL</b></a></h6>";
                                                            h += "</td>";
                                                        h += "</tr>";
                                                    }
                                                }
                                            }
                                        h += "</tbody>";
                                    h += "</table>";
                                h += "</div>";
                            h += "</div>";

                            Highcharts.chart('dashboard-chart', chart);
                            tabPane.el.find('.dashboard-additional').html(h);
                        }
                    }
                    Pace.stop();
                }
            }
        },
        error: function(data) {
            alert("Unable to render dashboard.\n\nError " + data.status + " (" + data.statusText + ")");
            Pace.stop();
        }
    });
};

