<?php
/**
 * Created by PhpStorm.
 * User: hamad
 * Date: 23/06/2019
 * Time: 1:39 AM
 */

namespace App\Handlers;

use Michielfb\Time\Time;
use Laravel\Nova\Fields\Text;
use Surelab\TelInput\TelInput;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Davidpiesse\NovaToggle\Toggle;
use Surelab\Percentage\Percentage;
use Surelab\NovaEditorV2\NovaEditorV2;
use Surelab\Settings\ValueObjects\SettingItem;
use Surelab\Settings\ValueObjects\SettingGroup;
use Surelab\Settings\Events\SettingsRegistering;

class SettingsRegisteringListener
{
    public function handle(SettingsRegistering $event)
    {
        $event->settingRegister
            ->group('general', function (SettingGroup $group) {
                $group->name(__('General'))
                    ->icon('fa fa-cog')
                    // ->item('hotel_address', function (SettingItem $item) {
                    //     $item->name(__('Address (ar)'))
                    //         ->field(Text::make('hotel_address')->help(
                    //             __('The address appears in invoices, receipts and contracts etc ..')
                    //         ))->priority(1);
                    // })
                    //ADD ENG ADDRESS
                    // ->item('hotel_en_address', function (SettingItem $item) {
                    //     $item->name(__('Address (en)'))
                    //         ->field(Text::make('hotel_en_address')->help(
                    //             __('The address appears in invoices, receipts and contracts etc ..')
                    //         ))->priority(1);
                    // })
                    // add liceence number
                    // ->item('hotel_liceence_number', function (SettingItem $item) {
                    //     $item->name(__('tourist license number'))
                    //         ->field(Text::make('hotel_liceence_number')->help(
                    //             __('tourist license number')
                    //         ))->priority(1);
                    // })
                    // add rating
                    // ->item('hotel_rating', function (SettingItem $item) {
                    //     $item->name(__('tourist classification category'))
                    //         ->field(Text::make('hotel_rating')->help(
                    //             __('tourist classification category')
                    //         ))->priority(1);
                    // })
                    ->item('reservation_default_status', function (SettingItem $item) {
                        $item->name(__('Reservation Default Status'))
                            ->field(
                                Select::make('reservation_default_status')
                                ->options([
                                    'awaiting-payment' => __('Awaiting Payment'),
                                    'confirmed' => __('Confirmed'),
                                ])
                                ->help(__('Select default status for reservation'))
                            );
                    })
                    // ->item('hotel_phone_number', function (SettingItem $item) {
                    //     $item->name(__('Mobile Number'))
                    //         ->field(Text::make('hotel_phone_number')->help(
                    //             __('Mobile number will appear everywhere in invoices, cash receipt, contracts , etc ..')
                    //         ))->priority(2);
                    // })
                    // ->item('hotel_email', function (SettingItem $item) {
                    //     $item->name(__('Email'))
                    //         ->field(Text::make('hotel_email')->help(
                    //             __('Email will appear everywhere in invoices, cash receipt, contracts , etc ..')
                    //         ))->priority(3);
                    // })
                    // ->item('hotel_website', function (SettingItem $item) {
                    //     $item->name(__('Website'))
                    //         ->field(Text::make('hotel_website')
                    //             ->help(__('Website will appear everywhere in invoices, cash receipt, contracts , etc ..')
                    //             ))->priority(4);
                    // })
                    ->item('day_start', function (SettingItem $item) {
                        $item->name(__('Day Start'))
                            ->field(Time::make('day_start')->help(
                                __('Reservation Start Hour')
                            ));
                    })
                    ->item('day_end', function (SettingItem $item) {
                        $item->name(__('Day End'))
                            ->field(Time::make('day_end')->help(
                                __('Reservation End Hour')
                            ));
                    })
                    ->item('enable_business_day_freeze', function (SettingItem $item) {
                        $item->name(__('Enable Business Day Finance Freeze'))
                            ->field(Toggle::make('enable_business_day_freeze')->help(
                                __('Freeze financial transactions on previous business days')
                            ));
                    })

                    ->item('business_day_hours', function (SettingItem $item) {
                        $item->name(__('Business Day Hours'))
                            ->field(Number::make('business_day_hours')
                                ->min(0)
                                ->max(24)
                                ->help(__('Business Day in Hours (0-24)'))
                            );
                    })
                // ->item('hotel_website', function (SettingItem $item) {
                //     $item->name(__('Website'))
                //         ->field(Text::make('hotel_website')
                //             ->help(
                //                 __('Website will appear everywhere in invoices, cash receipt, contracts , etc ..')
                //             ))->priority(4);
                // })
                    ->item('tax', function (SettingItem $item) {
                        $item->name(__('Tax'))
                            ->field(
                                Percentage::make('tax')
                                ->help(__('Tax percentage (%)'))
                            );
                    })
                    ->item('tax_number', function (SettingItem $item) {
                        $item->name(__('Tax Number'))
                            ->field(Text::make('tax_number')->help(
                                __('Tax number')
                            ));
                    })
                    ->item('accommodation_tax', function (SettingItem $item) {
                        $item->name(__('Accommodation Tax'))
                            ->field(Percentage::make('accommodation_tax')->help(
                                __('Accommodation percentage (%)')
                            )->withMeta(['placeholder' => __('Fee ratio')]));
                    })
                    // ->item('tourism_tax', function (SettingItem $item) {
                    //     $item->name(__('Tourism Tax'))
                    //         ->field(Percentage::make('tourism_tax')->help(
                    //             __('Tourism Tax Percentage (%)')
                    //         )->withMeta(['placeholder' => __('Percentage')]));
                    // })

                    ->item('automatic_under_cleaning', function (SettingItem $item) {
                        $item->name(__('Automatic Under Cleaning'))
                            ->field(Toggle::make('automatic_under_cleaning')->help(
                                __('Automatic convert unit status to under cleaning after checkout')
                            ));
                    })

                    ->item('transferـcustomer_to_another_unit_with_the_same_price', function (SettingItem $item) {
                        $item->name(__('Transfer customer to another unit with the same price'))
                            ->field(Toggle::make('transferـcustomer_to_another_unit_with_the_same_price')->help(
                                __('If the option is activated, the customer will be transferred from room to room at the same price as the old room')
                            ));
                    })

                    // ->item('time_12hr', function (SettingItem $item) {
                    //     $item->name(__('Search With Time 12 hrs'))
                    //         ->field(Toggle::make('time_12hr')->help(
                    //             __('If enabled search will be in 12 hours format am & pm')
                    //         ));
                    // })
                    ->item('name_on_documents', function (SettingItem $item) {
                        $item->name(__('Name on Documents'))
                            ->field(Toggle::make('name_on_documents')->help(
                                __('name will appear everywhere in invoices, cash receipt, contracts , etc ..')
                            )->withMeta(['placeholder' => __('Fee ratio')]));
                    })

//                    ->item('liquidation_of_receivables', function (SettingItem $item) {
//                        $item->name(__('Liquidation of dues before departure'))
//                            ->field(Toggle::make('liquidation_of_receivables')->help(
//                                __('No departure from the unit can be recorded until the dues are cleared')
//                            ));
//                    })
                    ->item('automatic_renewal_of_reservations', function (SettingItem $item) {
                        $item->name(__('Enable Auto Renew Reservations'))
                            ->field(Toggle::make('automatic_renewal_of_reservations')->help(
                                __('If the customer exceeds the exit date added in the settings, an additional day for the customer is calculated at the same price as the day in the unit, and an additional day is not counted if there is a reservation on the same day')
                            ));
                    })
                    ->item('daily_single_days', function (SettingItem $item) {
                        $item->name(__('Automatic renewal with reservation price'))
                            ->field(Toggle::make('daily_single_days')->help(
                                __('all nights price will be calculated according to reservation price (total price / nights = night price)')
                            ));
                    })
                    ->item('monthly_single_days', function (SettingItem $item) {
                        $item->name(__('monthly single days'))
                            ->field(Toggle::make('monthly_single_days')->help(
                                __('monthly single days description')
                            ));
                    })
                    //remove vat from monthly reservations
                    ->item('remove_vat_from_monthly_reservations', function (SettingItem $item) {
                        $item->name(__('Remove VAT from monthly reservations'))
                            ->field(Toggle::make('remove_vat_from_monthly_reservations')->help(
                                __('Remove VAT from monthly reservations')
                            ));
                    })
                ;
            })
            ->group('invoice', function (SettingGroup $group) {
                $group
                    ->name(__('Invoices & Contracts'))
                    ->icon('fa fa-hotel')
                    ->item('contract_person_in_charge_name', function (SettingItem $item) {
                        $item->name(__('Contract Person In Charge Name'))
                            ->field(
                                Text::make('contract_person_in_charge_name')
                                ->help(__('the name will appear in the contract'))
                            )->priority(5);
                    })
                    ->item('manager_name_in_deposit_transaction', function (SettingItem $item) {
                        $item->name(__('Manager Name In Deposit Transaction'))
                            ->field(
                                Text::make('manager_name_in_deposit_transaction')
                                ->help(__('the name will appear in the deposit transaction'))
                            );
                    })

                    ->item('manager_name_in_withdraw_transaction', function (SettingItem $item) {
                        $item->name(__('Manager Name In Withdraw Transaction'))
                            ->field(
                                Text::make('manager_name_in_withdraw_transaction')
                                ->help(__('the name will appear in the withdraw transaction'))
                            );
                    })
                    ->item('contract_notes', function (SettingItem $item) {
                        $item->name(__('Contract Notes (ar)'))
                            ->field(NovaEditorV2::make('contract_notes')->help(__('This note will appear under each contract')));
                    })
                    // english contract notes
                    ->item('contract_en_notes', function (SettingItem $item) {
                        $item->name(__('Contract Notes (en)'))
                            ->field(NovaEditorV2::make('contract_en_notes')->help(__('This note will appear under each contract')));
                    })
                    ->item('payment_voucher_notes', function (SettingItem $item) {
                        $item->name(__('Payment Voucher Notes'))
                            ->field(NovaEditorV2::make('payment_voucher_notes')->help(__('This note will appear under each payment voucher')));
                    })
                    ->item('cash_receipt_notes', function (SettingItem $item) {
                        $item->name(__('Cash Receipt Notes'))
                            ->field(NovaEditorV2::make('cash_receipt_notes')->help(__('This note will appear under each cash receipt')));
                    })
                    ->item('invoice_notes', function (SettingItem $item) {
                        $item->name(__('Invoice Notes'))
                            ->field(NovaEditorV2::make('invoice_notes')->help(__('This note will appear under each invoice')));
                    });
            })

            ->group('notification', function (SettingGroup $group) {
                $group->name(__('Notifications'))
                    ->icon('fa fa-sun')
                    ->item('alert_new_reservation', function (SettingItem $item) {
                        $item->name(__('Send an alert when a New Reservation'))
                            ->field(
                                \Silvanite\NovaFieldCheckboxes\Checkboxes::make('alert_new_reservation')
                                ->options([
                                    1 => __('Email'),
                                    2 => __('SMS'),
                                ])
                                ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                                })
                            );
                    })
                    ->item('alert_reservation_delete', function (SettingItem $item) {
                        $item->name(__('Send an alert when a Reservation Delete'))
                            ->field(
                                \Silvanite\NovaFieldCheckboxes\Checkboxes::make('alert_reservation_delete')
                                ->options([
                                    1 => __('Email'),
                                    2 => __('SMS'),
                                ])->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                                    return $requestAttribute;
                                })
                            );
                    })
                    ->item('alert_reservation_cancel', function (SettingItem $item) {
                        $item->name(__('Send an alert when a Reservation Cancel'))
                            ->field(
                                \Silvanite\NovaFieldCheckboxes\Checkboxes::make('alert_reservation_cancel')
                                ->options([
                                    1 => __('Email'),
                                    2 => __('SMS'),
                                ])->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                                    return $requestAttribute;
                                })
                            );
                    })
                    ->item('alert_daily_report', function (SettingItem $item) {
                        $item->name(__('Daily brief report'))
                            ->field(
                                \Silvanite\NovaFieldCheckboxes\Checkboxes::make('alert_daily_report')
                                ->options([
                                    1 => __('Email'),
                                    2 => __('SMS'),
                                ])->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                                    return $requestAttribute;
                                })
                            );
                    })
                    ->item('alert_email', function (SettingItem $item) {
                        $item->name(__('Email'))->key('alert_email');
                    })
                    ->item('alert_phone', function (SettingItem $item) {
                        //                        $item->name(__('Phone'))->key('alert_phone');
                        $item->name(__('Phone'))->field(TelInput::make('alert_phone')->defaultCountry('sa')->preferredCountries('sa'));
                    });
            })
            ->group('ratings', function (SettingGroup $group) {
                $group->name(__('Ratings'))
                    ->icon('fa fa-cog')
                    ->item('enable_ratings_email', function (SettingItem $item) {
                        $item->name(__('Enable Ratings Email'))
                            ->field(Toggle::make('enable_ratings_email'))
                            ->priority(38);
                    })
                    ->item('enable_ratings_sms', function (SettingItem $item) {
                        $item->name(__('Enable Ratings SMS'))
                            ->field(Toggle::make('enable_ratings_sms'))
                            ->priority(37);
                    })
                    ->item('introduction_text', function (SettingItem $item) {
                        $item->name(__('Introduction text'))
//                            ->field(Text::make('introduction_text')->withMeta(["value" => "شكراً لإقامتك لدينا، نود أن نستمع لتعليقاتكم من أجل تحسين تجربتك المستقبلية، لن يستغرق الأمر سوى بضغ دقائق"]));
                            ->field(Textarea::make('introduction_text')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'شكراً لإقامتك لدينا، نود أن نستمع لتعليقاتكم من أجل تحسين تجربتك المستقبلية، لن يستغرق الأمر سوى بضغ دقائق']))
                            ->priority(36);
                        })

                    ->item('title_of_the_first_question', function (SettingItem $item) {
                        $item->name(__('Text of the first question'))
//                            ->field(Text::make('text_of_the_first_question')->withMeta(["value" => "طاقم العمل"]));
                            ->field(Textarea::make('title_of_the_first_question')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'طاقم العمل']))
                            ->priority(35);
                    })
                    ->item('text_of_the_first_question', function (SettingItem $item) {
                        $item->name(__('Title of the first question'))
//                            ->field(Text::make('text_of_the_first_question')->withMeta(["value" => "طاقم العمل"]));
                            ->field(Text::make('text_of_the_first_question')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'طاقم العمل']))
                            ->priority(34);
                    })
                    ->item('enable_first_question', function (SettingItem $item) {
                        $item->name(__('Enable first question'))
                            ->field(Toggle::make('enable_first_question'))
                            ->priority(33);
                    })

                    ->item('title_of_the_second_question', function (SettingItem $item) {
                        $item->name(__('Text of the second question'))
//                            ->field(Text::make('text_of_the_second_question')->withMeta(["value" => "النظافة"]));
                            ->field(Textarea::make('title_of_the_second_question')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'النظافة']))
                            ->priority(32);
                    })
                    ->item('text_of_the_second_question', function (SettingItem $item) {
                        $item->name(__('Title of the second question'))
//                            ->field(Text::make('text_of_the_second_question')->withMeta(["value" => "النظافة"]));
                            ->field(Text::make('text_of_the_second_question')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'النظافة']))
                            ->priority(31);
                    })
                    ->item('enable_second_question', function (SettingItem $item) {
                        $item->name(__('Enable second question'))
                            ->field(Toggle::make('enable_second_question'))
                            ->priority(30);
                    })

                    ->item('title_of_the_third_question', function (SettingItem $item) {
                        $item->name(__('Text of the third question'))
//                            ->field(Text::make('text_of_the_second_question')->withMeta(["value" => "النظافة"]));
                            ->field(Textarea::make('title_of_the_third_question')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'المرافق']))
                            ->priority(29);
                    })
                    ->item('text_of_question_three', function (SettingItem $item) {
                        $item->name(__('Title of the third question'))
//                            ->field(Text::make('text_of_question_three')->withMeta(["value" => "المرافق"]));
                            ->field(Text::make('text_of_question_three')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'المرافق']))
                            ->priority(28);
                    })
                    ->item('enable_question_three', function (SettingItem $item) {
                        $item->name(__('Enable third question'))
                            ->field(Toggle::make('enable_question_three'))
                            ->priority(27);
                    })

                    ->item('title_of_the_fourth_question', function (SettingItem $item) {
                        $item->name(__('Text of the fourth question'))
                            ->field(Textarea::make('title_of_the_fourth_question')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'الراحة']))
                            ->priority(26);
                    })
                    ->item('text_of_question_four', function (SettingItem $item) {
                        $item->name(__('Title of the fourth question'))
                            ->field(Text::make('text_of_question_four')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'الراحة']))
                            ->priority(25);
                    })
                    ->item('enable_question_four', function (SettingItem $item) {
                        $item->name(__('Enable fourth question'))
                            ->field(Toggle::make('enable_question_four'))
                            ->priority(24);
                    })

                    ->item('title_of_the_fifth_question', function (SettingItem $item) {
                        $item->name(__('Text of the fifth question'))
                            ->field(Textarea::make('title_of_the_fifth_question')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'القيمة مقابل المال']))
                            ->priority(23);
                    })
                    ->item('text_of_question_five', function (SettingItem $item) {
                        $item->name(__('Title of the fifth question'))
                            ->field(Text::make('text_of_question_five')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'القيمة مقابل المال']))
                            ->priority(22);
                    })
                    ->item('enable_question_five', function (SettingItem $item) {
                        $item->name(__('Enable fifth question'))
                            ->field(Toggle::make('enable_question_five'))
                            ->priority(21);
                    })

                    ->item('title_of_the_sixth_question', function (SettingItem $item) {
                        $item->name(__('Text of the sixth question'))
                            ->field(Textarea::make('title_of_the_sixth_question')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'الموقع']))
                            ->priority(20);
                    })
                    ->item('the_text_of_the_sixth_question', function (SettingItem $item) {
                        $item->name(__('Title of the sixth question'))
                            ->field(Text::make('the_text_of_the_sixth_question')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'الموقع']))
                            ->priority(19);
                    })
                    ->item('enable_sixth_question', function (SettingItem $item) {
                        $item->name(__('Enable sixth question'))
                            ->field(Toggle::make('enable_sixth_question'))
                            ->priority(18);
                    })

                    ->item('title_of_the_seventh_question', function (SettingItem $item) {
                        $item->name(__('Text of the seventh question'))
                            ->field(Textarea::make('title_of_the_seventh_question')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'الخدمات الإضافية']))
                            ->priority(17);
                    })
                    ->item('the_text_of_the_seventh_question', function (SettingItem $item) {
                        $item->name(__('Title of the seventh question'))
                            ->field(Text::make('the_text_of_the_seventh_question')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'الخدمات الإضافية']))
                            ->priority(16);
                    })
                    ->item('enable_seventh_question', function (SettingItem $item) {
                        $item->name(__('Enable seventh question'))
                            ->field(Toggle::make('enable_seventh_question'))
                            ->priority(15);
                    })

                    ->item('title_of_the_eighth_question', function (SettingItem $item) {
                        $item->name(__('Text of the eighth question'))
                            ->field(Textarea::make('title_of_the_eighth_question')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'التعامل مع العملاء']))
                            ->priority(14);
                    })
                    ->item('the_text_of_the_eighth_question', function (SettingItem $item) {
                        $item->name(__('Title of the eighth question'))
                            ->field(Text::make('the_text_of_the_eighth_question')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'التعامل مع العملاء']))
                            ->priority(13);
                    })
                    ->item('enable_eighth_question', function (SettingItem $item) {
                        $item->name(__('Enable eighth question'))
                            ->field(Toggle::make('enable_eighth_question'))
                            ->priority(12);
                    })

                    ->item('title_of_the_ninth_question', function (SettingItem $item) {
                        $item->name(__('Text of the ninth question'))
                            ->field(Textarea::make('title_of_the_ninth_question')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'التعامل مع العملاء']))
                            ->priority(11);
                    })
                    ->item('the_text_of_the_ninth_question', function (SettingItem $item) {
                        $item->name(__('Title of the ninth question'))
                            ->field(Text::make('the_text_of_the_ninth_question')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'التعامل مع العملاء']))
                            ->priority(10);
                    })
                    ->item('enable_ninth_question', function (SettingItem $item) {
                        $item->name(__('Enable ninth question'))
                            ->field(Toggle::make('enable_ninth_question'))
                            ->priority(9);
                    })

                    ->item('title_of_the_tenth_question', function (SettingItem $item) {
                        $item->name(__('Text of the tenth question'))
                            ->field(Textarea::make('title_of_the_tenth_question')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'التعامل مع العملاء']))
                            ->priority(8);
                    })
                    ->item('the_text_of_the_tenth_question', function (SettingItem $item) {
                        $item->name(__('Title of the tenth question'))
                            ->field(Text::make('the_text_of_the_tenth_question')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'التعامل مع العملاء']))
                            ->priority(7);
                    })
                    ->item('enable_tenth_question', function (SettingItem $item) {
                        $item->name(__('Enable tenth question'))
                            ->field(Toggle::make('enable_tenth_question'))
                            ->priority(6);
                    })

                    ->item('first_custom_message', function (SettingItem $item) {
                        $item->name(__('First custom message'))
                            ->field(Textarea::make('first_custom_message')->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 'التعامل مع العملاء']))
                            ->priority(5);
                    })
                    ->item('enable_first_custom_message', function (SettingItem $item) {
                        $item->name(__('Enable first custom message'))
                            ->field(Toggle::make('enable_first_custom_message'))
                            ->priority(4);
                    })
                    ->item('send_rating_after', function (SettingItem $item) {
                        $item->name(__('Submit an evaluation request form after check-out b / h'))
                            ->field(Number::make('send_rating_after')->min(0)->withMeta($item->getValue() != null ? ['value' => $item->getValue()] : ['value' => 0]))
                            ->priority(1);
                    })
                ;
            })
        ;
    }
}
