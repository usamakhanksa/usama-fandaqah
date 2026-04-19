import { SweetModal } from 'sweet-modal-vue';
import VueTelInput from 'vue-tel-input';

import VueLazyInput from 'vue-lazy-input'
import BreadCrumb from "./components/BreadCrumb";
import VTooltip from 'v-tooltip'
import VueSignaturePad from 'vue-signature-pad';

Nova.booting((Vue, router, store) => {
    Vue.use(VueLazyInput);
    Vue.use(VTooltip);
    Vue.use(VueSignaturePad);
    const routes = [
        {
            name: 'settings',
            path: '/settings',
            component: require('./components/Settings'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.invoices',
            path: '/settings/invoices',
            component: require('./components/sections/Invoices'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.notifications',
            path: '/settings/notifications',
            component: require('./components/sections/Notifications'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
    {
        name: 'channel-manager',
        path: '/channel-manager',
        component: require('./components/ChannelManager'),
        permissions: ['view settings'],
        beforeEnter: (to, from, next) => {
            next();

        }
    },
    {
        name: 'channel-units',
        path: '/channel-units',
        component: require('./components/Units'),
        permissions: ['view settings'],
        beforeEnter: (to, from, next) => {
            next();

        }
    },
    {
        name: 'channel-availbility',
        path: '/channel-availbility',
        component: require('./components/Availbility'),
        permissions: ['view settings'],
        beforeEnter: (to, from, next) => {
            next();

        }

    },
    {
        name: 'channel-reservations',
        path: '/channel-reservations',
        component: require('./components/Reservations'),
        permissions: ['view settings'],
        beforeEnter: (to, from, next) => {
            next();

        }

    },

        {
            name: 'settings.integrations',
            path: '/settings/integrations',
            component: require('./components/sections/Integration'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'settings.general',
            path: '/settings/general',
            component: require('./components/sections/Hotels'),
            props: route => {
                return {
                    resourceName: 'teams',
                    viaResource: route.query.viaResource,
                    viaResourceId: route.query.viaResourceId,
                    viaRelationship: route.query.viaRelationship,
                }
            },
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'settings.ratings',
            path: '/settings/ratings',
            component: require('./components/sections/Ratings'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'edit-hotel',
            path: '/settings/general/:resourceName/:resourceId/edit',
            component: require('./components/sections/hotels/Update'),
            props: route => {
                return {
                    resourceName: route.params.resourceName,
                    resourceId: route.params.resourceId,
                    viaResource: route.query.viaResource,
                    viaResourceId: route.query.viaResourceId,
                    viaRelationship: route.query.viaRelationship,
                }
            },
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'create-hotel',
            path: '/settings/general/:resourceName/new',
            component: require('./components/sections/hotels/Create'),
            props: route => {
                return {
                    resourceName: route.params.resourceName,
                    viaResource: route.query.viaResource,
                    viaResourceId: route.query.viaResourceId,
                    viaRelationship: route.query.viaRelationship,
                }
            },
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.counter',
            path: '/settings/counter',
            component: require('./components/sections/Counter'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.website',
            path: '/settings/website',
            component: require('./components/sections/Website'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.website.domain',
            path: '/settings/website/domain',
            component: require('./components/sections/website/Domain'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.website.banner',
            path: '/settings/website/banner',
            component: require('./components/sections/website/Banner'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.website.logo',
            path: '/settings/website/logo',
            component: require('./components/sections/website/Logo'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.website.gallery',
            path: '/settings/website/gallery',
            // component: require('./components/sections/website/Gallery'),
            component: require('./components/sections/website/PhotoAlbum'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'settings.website.pages',
            path: '/settings/website/pages',
            component: require('./components/sections/website/pages/Index'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'settings.website.pages.new',
            path: '/settings/website/pages/new',
            component: require('./components/sections/website/pages/Add'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.website.pages.details',
            path: `/settings/website/pages/:id/details`,
            component: require('./components/sections/website/pages/Details'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'settings.website.pages.edit',
            path: `/settings/website/pages/:id/edit`,
            component: require('./components/sections/website/pages/Edit'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'settings.website.social',
            path: '/settings/website/social',
            component: require('./components/sections/website/Social'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.website.phrases',
            path: '/settings/website/phrases',
            component: require('./components/sections/website/Phrases'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.website.cancellation_policy',
            path: '/settings/website/cancellation-policy',
            component: require('./components/sections/website/CancellationPolicy'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.website.general_settings',
            path: '/settings/website/general-settings',
            component: require('./components/sections/website/GeneralSettings'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.website.payment_options_settings',
            path: '/settings/website/payment-options-settings',
            component: require('./components/sections/website/PaymentOptionsSettings'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.website.language_settings',
            path: '/settings/website/language-settings',
            component: require('./components/sections/website/LanguageSettings'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.website.distinguished_index_unit_categories',
            path: '/settings/website/index-unit-categories',
            component: require('./components/sections/website/DistinguishedIndexUnitCategories'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.website.slider_settings',
            path: '/settings/website/slider-settings',
            component: require('./components/sections/website/SliderSettings'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'settings.website.about_us',
            path: '/settings/website/about-us',
            component: require('./components/sections/website/AboutUsSection'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.website.intro_video',
            path: '/settings/website/intro_video',
            component: require('./components/sections/website/IntroVideo'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.website.seo',
            path: '/settings/website/seo',
            component: require('./components/sections/website/Seo'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.activity_logs',
            path: '/settings/activity-logs',
            component: require('./components/sections/activity-log/Index'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },

        {
            name: 'settings.activity_log_info',
            path: '/settings/activity-log-info/:id',
            component: require('./components/sections/activity-log/Info'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.profile',
            path: '/profile',
            component: require('./components/sections/profile/Profile'),
            meta: {
                breadcrumb: [
                    {
                        name: 'Home',
                        link: '/'
                    },
                    {
                        name: 'My Account',
                        link: '/settings/profile'
                    }

                ]
            },
            // permissions: ['view settings'],
            // beforeEnter: (to, from, next) => {
                // if (Nova.app.$hasPermission('view settings')) {
                //     next();
                // } else {
                //     next('/403');
                // }
            // }
        },
        {
            name: 'settings.profile',
            path: '/profile/edit',
            component: require('./components/sections/profile/ProfileEdit'),
            meta: {
                breadcrumb: [
                    {
                        name: 'Home',
                        link: '/'
                    },
                    {
                        name: 'My Account',
                        link: '/settings/profile'
                    },
                    {
                        name: 'Update My Information',
                        link: '/settings/profile/edit'
                    },


                ]
            },
            // permissions: ['view settings'],
            // beforeEnter: (to, from, next) => {
                // if (Nova.app.$hasPermission('view settings')) {
                //     next();
                // } else {
                //     next('/403');
                // }
            // }
        },
        {
            name: 'settings.facility-settings',
            path: '/settings/facility-settings',
            component: require('./components/sections/FacilitySettings'),
            // permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.reservation-services',
            path: '/settings/reservation-services',
            component: require('./components/sections/ReservationServices'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
        {
            name: 'settings.maintenance-settings',
            path: '/settings/maintenance-settings',
            component: require('./components/sections/MaintenanceSettings'),
            permissions: ['view settings'],
            beforeEnter: (to, from, next) => {
                if (Nova.app.$hasPermission('view settings')) {
                    next();
                } else {
                    next('/403');
                }
            }
        },
    ];
    router.addRoutes(routes);
    Vue.component('integration-alraedah', require('./components/sections/integrations/alraedah'));
    Vue.component('integration-unifonic', require('./components/sections/integrations/unifonic'));
    Vue.component('staah-channel-manager', require('./components/sections/integrations/staah-channel-manager'));
    Vue.component('integration-scth', require('./components/sections/integrations/scth'));
    Vue.component('integration-shms', require('./components/sections/integrations/shms'));
    Vue.component('integration-zatca-phase-two', require('./components/sections/integrations/zatca-phase-two'));
    Vue.component('sweet-modal', SweetModal);
    Vue.component('bread-crumb', BreadCrumb);
    Vue.component('paginationnew', require('laravel-vue-pagination'));



    Vue.component('hotels-table', require('./components/sections/hotels/HotelTable'));
    Vue.component('hotels-table-row', require('./components/sections/hotels/HotelTableRow'));
    Vue.component('create-hotel-button', require('./components/sections/hotels/CreateHotelButton'));
    Vue.component('vue-tel-input', VueTelInput);

})
