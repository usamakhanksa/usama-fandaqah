<template>
    <div>
        <!-- Edit Service Transaction -->
        <sweet-modal
            class="edit-service-transaction"
            ref="editServiceModal"
            @close="editModalClosed"
            :enable-mobile-fullscreen="false"
            :pulse-on-block="false"
            :title="__('Edit Service Transaction')"
            overlay-theme="dark"
        >
            <div class="addServiceinside overflowautoforsweet relative">
                <loading
                    :active.sync="isLoading"
                    :can-cancel="true"
                    :loader="'spinner'"
                    :color="'#7e7d7f'"
                    :is-full-page="fullPage"
                ></loading>
                <integration-input-group
                    :transaction="transaction"
                    :enabled="team.integration_zatca_phase_two"
                >
                    <div
                        class="input_group"
                        v-if="
                            transaction &&
                            transaction.payable_type == 'App\\Team'
                        "
                    >
                        <label for="customer_name">{{
                            __("Customer Name")
                        }}</label>
                        <input
                            id="customer_name"
                            v-model="customer_name"
                            type="text"
                        />
                    </div>
                    <!-- input_group -->

                    <div
                        class="input_group"
                        v-if="
                            transaction &&
                            transaction.payable_type == 'App\\Team'
                        "
                    >
                        <label for="address">{{ __("Address") }}</label>

                        <input id="address" v-model="address" type="text" />
                    </div>
                    <!-- input_group -->

                    <div
                        class="input_group"
                        v-if="
                            transaction &&
                            transaction.payable_type == 'App\\Team'
                        "
                    >
                        <label for="tax_number">{{ __("Tax number") }}</label>
                        <input
                            id="tax_number"
                            v-model="tax_number"
                            type="text"
                        />
                    </div>
                    <!-- input_group -->
                    <div class="input_group" v-if="canEditDate">
                        <TransactionDate
                            :value="transaction_date"
                            :locale="locale"
                            :disabled="!canEditDate"
                            v-if="
                                transaction &&
                                transaction.payable_type == 'App\\Team'
                            "
                        />
                    </div>
                    <div class="input_group" v-else>
                        <label for="tax_number">{{
                            __("Transaction Date")
                        }}</label>
                        <p>{{ transaction_date }}</p>
                    </div>
                    <div v-if="!items.length && !isLoading" class="nodata">
                        {{
                            __(
                                "All services has been removed temporarily , by clicking the update btn the actual remove will be triggered"
                            )
                        }}
                    </div>
                    <!-- Action Table for Playing with services -->
                    <div v-if="items.length" class="table_of_services relative">
                        <!-- Loader -->
                        <!-- <template class="relative">
                            <loading :active.sync="isLoading"
                                     :is-full-page="false">
                            </loading>
                        </template> -->

                        <div
                            class="form_has_wrong_inputs"
                            v-if="showValidationMsg"
                        >
                            {{
                                __(
                                    "Please make sure to add valid price & quantity for each service"
                                )
                            }}
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>{{ __("Service") }}</th>
                                    <th>{{ __("Price") }}</th>
                                    <th>{{ __("Qty") }}</th>
                                    <th>{{ __("Sub total") }}</th>
                                    <th
                                        style="width: 20%"
                                        v-if="
                                            unitServiceTaxInformation.vat_percentage
                                        "
                                    >
                                        {{ __("VAT") }} (%{{
                                            unitServiceTaxInformation.vat_percentage
                                        }})
                                    </th>
                                    <th
                                        style="width: 20%"
                                        v-if="
                                            unitServiceTaxInformation.tourism_percentage
                                        "
                                    >
                                        {{ __("TTX") }} (%{{
                                            unitServiceTaxInformation.tourism_percentage
                                        }})
                                    </th>
                                    <th colspan="2">
                                        {{ __("Total With Tax") }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in items" :key="index">
                                    <td>{{ item.text }}</td>

                                    <td v-if="canChangeServicePrice">
                                        <input
                                            @input="selected(item)"
                                            v-model="item.price"
                                            type="number"
                                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                        />
                                    </td>
                                    <td v-else>{{ item.price }}</td>
                                    <td>
                                        <input
                                            @input="selected(item)"
                                            v-model="item.qty"
                                            type="number"
                                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"
                                        />
                                    </td>
                                    <td>
                                        <template
                                            ><b>{{
                                                parseFloat(
                                                    item.subTotal
                                                ).toFixed(2)
                                            }}</b></template
                                        >
                                    </td>

                                    <td
                                        v-if="
                                            unitServiceTaxInformation.vat_percentage
                                        "
                                    >
                                        <div class="checkbox">
                                            <p>
                                                {{
                                                    parseFloat(
                                                        items[index].vat
                                                    ).toFixed(2)
                                                }}
                                            </p>
                                            <label class="switch"
                                                ><input
                                                    @change="
                                                        vatSliderButtonChanged(
                                                            $event,
                                                            item
                                                        )
                                                    "
                                                    v-model="item.vatIsChecked"
                                                    type="checkbox" />
                                                <span
                                                    class="slider round"
                                                ></span
                                            ></label>
                                        </div>
                                    </td>
                                    <td
                                        v-if="
                                            unitServiceTaxInformation.tourism_percentage
                                        "
                                    >
                                        <div class="checkbox">
                                            <p>
                                                {{
                                                    parseFloat(
                                                        items[index].ttx
                                                    ).toFixed(2)
                                                }}
                                            </p>
                                            <label class="switch"
                                                ><input
                                                    type="checkbox"
                                                    @change="
                                                        ttxSliderButtonChanged(
                                                            $event,
                                                            item
                                                        )
                                                    "
                                                    v-model="
                                                        item.ttxIsChecked
                                                    " />
                                                <span
                                                    class="slider round"
                                                ></span
                                            ></label>
                                        </div>
                                    </td>

                                    <td>
                                        <b>{{
                                            parseFloat(
                                                items[index].totalGeneralSum
                                            ).toFixed(2)
                                        }}</b>
                                    </td>
                                    <td>
                                        <button
                                            v-if="ifNotIssuedInvoiceZatca()"
                                            type="button"
                                            @click="removeService(items[index])"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="18.688"
                                                height="20.049"
                                                viewBox="0 0 18.688 20.049"
                                            >
                                                <g
                                                    transform="translate(-17.379)"
                                                >
                                                    <g
                                                        transform="translate(17.379 3.01)"
                                                    >
                                                        <g
                                                            transform="translate(0)"
                                                        >
                                                            <path
                                                                d="M306.717,175.114l-1.569-.058-.34,9.292,1.569.057Z"
                                                                transform="translate(-293.552 -171.211)"
                                                                fill="#ff2626"
                                                            />
                                                            <rect
                                                                width="1.57"
                                                                height="9.292"
                                                                transform="translate(8.559 3.874)"
                                                                fill="#ff2626"
                                                            />
                                                            <path
                                                                d="M160.329,184.341l-.34-9.292-1.569.058.34,9.292Z"
                                                                transform="translate(-152.896 -171.204)"
                                                                fill="#ff2626"
                                                            />
                                                            <path
                                                                d="M17.379,76.867v1.57h1.636l1.3,14.752a.785.785,0,0,0,.782.716H32.324a.785.785,0,0,0,.782-.717l1.3-14.752h1.663v-1.57ZM31.6,92.335h-9.79l-1.223-13.9H32.828Z"
                                                                transform="translate(-17.379 -76.867)"
                                                                fill="#ff2626"
                                                            />
                                                        </g>
                                                    </g>
                                                    <g
                                                        transform="translate(22.849)"
                                                    >
                                                        <g
                                                            transform="translate(0)"
                                                        >
                                                            <path
                                                                d="M163.515,0h-5.13a1.31,1.31,0,0,0-1.309,1.309V3.8h1.57V1.57h4.607V3.8h1.57V1.309A1.31,1.31,0,0,0,163.515,0Z"
                                                                transform="translate(-157.076)"
                                                                fill="#ff2626"
                                                            />
                                                        </g>
                                                    </g>
                                                </g>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="items.length" class="w-full">
                        <div class="rounded w-full flex flex-wrap my-3">
                            <div
                                class="sm:w-1/1 md:w-1/3 lg:w-1/5 xl:w-1/5 totle_cost"
                            >
                                <div class="relative">
                                    <h2
                                        class="block capitalize text-md mb-2 w-full"
                                    >
                                        {{ __("Sub total") }}
                                    </h2>
                                    <h4 class="text-base font-normal w-full">
                                        <b class="text-black-500">{{
                                            sumSubTotal
                                        }}</b>
                                    </h4>
                                </div>
                            </div>
                            <div
                                class="sm:w-1/1 md:w-1/3 lg:w-1/5 xl:w-1/5 totle_cost"
                                v-if="unitServiceTaxInformation.vat_percentage"
                            >
                                <div class="relative">
                                    <h2
                                        class="block capitalize text-md mb-2 w-full"
                                    >
                                        {{ __("Total Vat") }}
                                    </h2>
                                    <h4 class="text-base font-normal w-full">
                                        <b class="text-black-500">{{
                                            sumVatTotal
                                        }}</b>
                                    </h4>
                                </div>
                            </div>
                            <div
                                class="sm:w-1/1 md:w-1/3 lg:w-1/5 xl:w-1/5 totle_cost"
                                v-if="
                                    unitServiceTaxInformation.tourism_percentage
                                "
                            >
                                <div class="relative">
                                    <h2
                                        class="block capitalize text-md mb-2 w-full"
                                    >
                                        {{ __("Total Ttx") }}
                                    </h2>
                                    <h4 class="text-base font-normal w-full">
                                        <b class="text-black-500">{{
                                            sumTtxTotal
                                        }}</b>
                                    </h4>
                                </div>
                            </div>
                            <div
                                class="sm:w-1/1 md:w-1/3 lg:w-1/5 xl:w-1/5 totle_cost"
                            >
                                <div class="relative">
                                    <h2
                                        class="block capitalize text-md mb-2 w-full"
                                    >
                                        {{ __("Sum of Taxes") }}
                                    </h2>
                                    <h4 class="text-base font-normal w-full">
                                        <b class="text-black-500">{{
                                            parseFloat(sumTotalTaxes).toFixed(2)
                                        }}</b>
                                    </h4>
                                </div>
                            </div>
                            <div
                                class="sm:w-1/1 md:w-1/3 lg:w-1/5 xl:w-1/5 totle_cost"
                            >
                                <div class="relative">
                                    <h2
                                        class="block capitalize text-md mb-2 w-full"
                                    >
                                        {{ __("Sum of total with tax") }}
                                    </h2>
                                    <h4 class="text-base font-normal w-full">
                                        <b class="text-black-500">{{
                                            sumGeneralTotalWithTaxes
                                        }}</b>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </integration-input-group>

                <integration-input-actions
                    :disableUpdate="disableUpdate"
                    :updateServices="updateServices"
                    :spinnerLoadOnEdit="spinnerLoadOnEdit"
                    :spinnerLoadOnEditZatca="spinnerLoadOnEditZatca"
                    :locale="locale"
                    :zatcaEnabled="team.integration_zatca_phase_two"
                    :transaction="transaction"
                    :pushToZatca="pushToZatca"
                    :handleModal="handleModal"
                    :handleLoading="handleLoading"
                />

                <template v-if="team.integration_zatca_phase_two">
                    <hr class="custom-hr" />
                    <div
                        class="d-flex align-items-center justify-content-center mt-3"
                    >
                        <svg
                            width="40px"
                            height="40px"
                            viewBox="-2.4 -2.4 28.80 28.80"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                            stroke="#919191"
                            stroke-width="0.072"
                        >
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g
                                id="SVGRepo_tracerCarrier"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke="#CCCCCC"
                                stroke-width="0.288"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M6.55281 1.60553C7.10941 1.32725 7.77344 1 9 1C10.2265 1 10.8906 1.32722 11.4472 1.6055L11.4631 1.61347C11.8987 1.83131 12.2359 1.99991 13 1.99993C14.2371 1.99998 14.9698 1.53871 15.2141 1.35512C15.5944 1.06932 16.0437 1.09342 16.3539 1.2369C16.6681 1.38223 17 1.72899 17 2.24148L17 13H20C21.6562 13 23 14.3415 23 15.999V19C23 19.925 22.7659 20.6852 22.3633 21.2891C21.9649 21.8867 21.4408 22.2726 20.9472 22.5194C20.4575 22.7643 19.9799 22.8817 19.6331 22.9395C19.4249 22.9742 19.2116 23.0004 19 23H5C4.07502 23 3.3148 22.7659 2.71092 22.3633C2.11331 21.9649 1.72739 21.4408 1.48057 20.9472C1.23572 20.4575 1.11827 19.9799 1.06048 19.6332C1.03119 19.4574 1.01616 19.3088 1.0084 19.2002C1.00194 19.1097 1.00003 19.0561 1 19V2.24146C1 1.72899 1.33184 1.38223 1.64606 1.2369C1.95628 1.09341 2.40561 1.06931 2.78589 1.35509C3.03019 1.53868 3.76289 1.99993 5 1.99993C5.76415 1.99993 6.10128 1.83134 6.53688 1.6135L6.55281 1.60553ZM3.00332 19L3 3.68371C3.54018 3.86577 4.20732 3.99993 5 3.99993C6.22656 3.99993 6.89059 3.67269 7.44719 3.39441L7.46312 3.38644C7.89872 3.1686 8.23585 3 9 3C9.76417 3 10.1013 3.16859 10.5369 3.38643L10.5528 3.39439C11.1094 3.67266 11.7734 3.9999 13 3.99993C13.7927 3.99996 14.4598 3.86581 15 3.68373V19C15 19.783 15.1678 20.448 15.4635 21H5C4.42498 21 4.0602 20.8591 3.82033 20.6992C3.57419 20.5351 3.39761 20.3092 3.26943 20.0528C3.13928 19.7925 3.06923 19.5201 3.03327 19.3044C3.01637 19.2029 3.00612 19.1024 3.00332 19ZM19.3044 20.9667C19.5201 20.9308 19.7925 20.8607 20.0528 20.7306C20.3092 20.6024 20.5351 20.4258 20.6992 20.1797C20.8591 19.9398 21 19.575 21 19V15.999C21 15.4474 20.5529 15 20 15H17L17 19C17 19.575 17.1409 19.9398 17.3008 20.1797C17.4649 20.4258 17.6908 20.6024 17.9472 20.7306C18.2075 20.8607 18.4799 20.9308 18.6957 20.9667C18.8012 20.9843 18.8869 20.9927 18.9423 20.9967C19.0629 21.0053 19.1857 20.9865 19.3044 20.9667Z"
                                    fill="#bfbfbf"
                                ></path>
                                <path
                                    d="M5 8C5 7.44772 5.44772 7 6 7H12C12.5523 7 13 7.44772 13 8C13 8.55229 12.5523 9 12 9H6C5.44772 9 5 8.55229 5 8Z"
                                    fill="#bfbfbf"
                                ></path>
                                <path
                                    d="M5 12C5 11.4477 5.44772 11 6 11H12C12.5523 11 13 11.4477 13 12C13 12.5523 12.5523 13 12 13H6C5.44772 13 5 12.5523 5 12Z"
                                    fill="#bfbfbf"
                                ></path>
                                <path
                                    d="M5 16C5 15.4477 5.44772 15 6 15H12C12.5523 15 13 15.4477 13 16C13 16.5523 12.5523 17 12 17H6C5.44772 17 5 16.5523 5 16Z"
                                    fill="#bfbfbf"
                                ></path>
                            </g>
                            <g id="SVGRepo_iconCarrier">
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M6.55281 1.60553C7.10941 1.32725 7.77344 1 9 1C10.2265 1 10.8906 1.32722 11.4472 1.6055L11.4631 1.61347C11.8987 1.83131 12.2359 1.99991 13 1.99993C14.2371 1.99998 14.9698 1.53871 15.2141 1.35512C15.5944 1.06932 16.0437 1.09342 16.3539 1.2369C16.6681 1.38223 17 1.72899 17 2.24148L17 13H20C21.6562 13 23 14.3415 23 15.999V19C23 19.925 22.7659 20.6852 22.3633 21.2891C21.9649 21.8867 21.4408 22.2726 20.9472 22.5194C20.4575 22.7643 19.9799 22.8817 19.6331 22.9395C19.4249 22.9742 19.2116 23.0004 19 23H5C4.07502 23 3.3148 22.7659 2.71092 22.3633C2.11331 21.9649 1.72739 21.4408 1.48057 20.9472C1.23572 20.4575 1.11827 19.9799 1.06048 19.6332C1.03119 19.4574 1.01616 19.3088 1.0084 19.2002C1.00194 19.1097 1.00003 19.0561 1 19V2.24146C1 1.72899 1.33184 1.38223 1.64606 1.2369C1.95628 1.09341 2.40561 1.06931 2.78589 1.35509C3.03019 1.53868 3.76289 1.99993 5 1.99993C5.76415 1.99993 6.10128 1.83134 6.53688 1.6135L6.55281 1.60553ZM3.00332 19L3 3.68371C3.54018 3.86577 4.20732 3.99993 5 3.99993C6.22656 3.99993 6.89059 3.67269 7.44719 3.39441L7.46312 3.38644C7.89872 3.1686 8.23585 3 9 3C9.76417 3 10.1013 3.16859 10.5369 3.38643L10.5528 3.39439C11.1094 3.67266 11.7734 3.9999 13 3.99993C13.7927 3.99996 14.4598 3.86581 15 3.68373V19C15 19.783 15.1678 20.448 15.4635 21H5C4.42498 21 4.0602 20.8591 3.82033 20.6992C3.57419 20.5351 3.39761 20.3092 3.26943 20.0528C3.13928 19.7925 3.06923 19.5201 3.03327 19.3044C3.01637 19.2029 3.00612 19.1024 3.00332 19ZM19.3044 20.9667C19.5201 20.9308 19.7925 20.8607 20.0528 20.7306C20.3092 20.6024 20.5351 20.4258 20.6992 20.1797C20.8591 19.9398 21 19.575 21 19V15.999C21 15.4474 20.5529 15 20 15H17L17 19C17 19.575 17.1409 19.9398 17.3008 20.1797C17.4649 20.4258 17.6908 20.6024 17.9472 20.7306C18.2075 20.8607 18.4799 20.9308 18.6957 20.9667C18.8012 20.9843 18.8869 20.9927 18.9423 20.9967C19.0629 21.0053 19.1857 20.9865 19.3044 20.9667Z"
                                    fill="#bfbfbf"
                                ></path>
                                <path
                                    d="M5 8C5 7.44772 5.44772 7 6 7H12C12.5523 7 13 7.44772 13 8C13 8.55229 12.5523 9 12 9H6C5.44772 9 5 8.55229 5 8Z"
                                    fill="#bfbfbf"
                                ></path>
                                <path
                                    d="M5 12C5 11.4477 5.44772 11 6 11H12C12.5523 11 13 11.4477 13 12C13 12.5523 12.5523 13 12 13H6C5.44772 13 5 12.5523 5 12Z"
                                    fill="#bfbfbf"
                                ></path>
                                <path
                                    d="M5 16C5 15.4477 5.44772 15 6 15H12C12.5523 15 13 15.4477 13 16C13 16.5523 12.5523 17 12 17H6C5.44772 17 5 16.5523 5 16Z"
                                    fill="#bfbfbf"
                                ></path>
                            </g>
                        </svg>
                        <h3 class="heading-3">{{ __("Receipts") }}</h3>
                    </div>
                    
                    <div class="table_area" v-if="transaction.payable_type !== PAYABLE_RESERVATION">
                        <div class="table-responsive relative">
                            <table
                                class="table w-full"
                                cellpadding="0"
                                cellspacing="0"
                            >
                                <thead>
                                    <tr>
                                        <th>{{ __("Timestamp") }}</th>
                                        <th>{{ __("Zatca Invoice Number") }}</th>
                                        <th>{{ __("Receipt Type") }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="note in notes">
                                        <td class="text-center">
                                            {{ note.created_at }}
                                        </td>
                                        <td v-if="note.payload && note.payload !== null" class="text-center">
                                            {{
                                                note.payload.invoice_number !== undefined ?
                                                note.payload.invoice_number :
                                                "-"
                                            }}
                                        </td>
                                        <td>
                                            <div
                                                class="d-flex align-items-center justify-content-center gap-2"
                                            >
                                                {{
                                                    note.type == "debit note"
                                                        ? "invoice"
                                                        : note.type
                                                }}
                                                <invoice-attachments
                                                    v-if="note.payload"
                                                    :invoiceXML="
                                                        note.payload.invoice
                                                    "
                                                    :meta="constructMeta(note)"
                                                    @loading="handleLoading"
                                                />
                                                <div v-else>
                                                    <svg
                                                        width="20px"
                                                        height="20px"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                    >
                                                        <g
                                                            id="SVGRepo_bgCarrier"
                                                            stroke-width="0"
                                                        ></g>
                                                        <g
                                                            id="SVGRepo_tracerCarrier"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke="#CCCCCC"
                                                            stroke-width="0.336"
                                                        ></g>
                                                        <g
                                                            id="SVGRepo_iconCarrier"
                                                        >
                                                            <path
                                                                d="M12 16H12.01M12 8V12M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z"
                                                                stroke="#ff8585"
                                                                stroke-width="2.4"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                            ></path>
                                                        </g>
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-else>
                        <alert-component :message=" __('zatca receipt message in edit pos')"/>
                    </div>
                </template>
            </div>
        </sweet-modal>
    </div>
</template>

<script>
import TransactionDate from "../TransactionDate";
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/vue-loading.css";
import InvoiceAttachments from "./InvoiceAttachments.vue";
import IntegrationInputGroup from "./IntegrationInputGroup.vue";
import IntegrationInputActions from "./IntegrationInputActions.vue";
import Common from "../../../../../Common";
export default {
    components: {
        Loading,
        TransactionDate,
        InvoiceAttachments,
        IntegrationInputGroup,
        IntegrationInputActions,
        AlertComponent: Common.AlertComponent
    },
    data: () => {
        return {
            unitServiceTaxInformation: null,
            transaction: null,
            locale: Nova.config.local,
            items: [],
            obj: {
                text: null,
                price: 0,
                qty: Math.floor(0),
                subTotal: 0,
                vat: 0,
                ttx: 0,
                vatIsChecked: false,
                ttxIsChecked: false,
                totalGeneralSum: Number(0),
                id: null,
            },
            isLoading: false,
            spinnerLoadOnEdit: false,
            spinnerLoadOnEditZatca: false,
            disableUpdate: false,
            disableZatca: false,
            showValidationMsg: false,
            canChangeServicePrice: false,
            updateInProgress: false,
            transaction_date: null,
            customer_name: "",
            address: null,
            tax_number: null,
            canChangeDiscountPrice: false,
            integrations: [],
            canEditDate: true,
            notes: [],
            service_log_number: null,
            team: Nova.app.currentTeam,
            PAYABLE_RESERVATION: "App\\Reservation"

        };
    },
    computed: {
        sumSubTotal() {
            let sumSubTotal = 0;
            for (let i = this.items.length - 1; i >= 0; --i) {
                sumSubTotal += this.items[i].subTotal;
            }
            return sumSubTotal;
        },
        sumVatTotal() {
            let vatTotal = 0;
            for (let i = this.items.length - 1; i >= 0; --i) {
                if (this.items[i].vatIsChecked) {
                    vatTotal += this.items[i].vat;
                }
            }
            return parseFloat(vatTotal).toFixed(2);
        },
        sumTtxTotal() {
            let ttxTotal = 0;
            for (let i = this.items.length - 1; i >= 0; --i) {
                if (this.items[i].ttxIsChecked) {
                    ttxTotal += this.items[i].ttx;
                }
            }
            return parseFloat(ttxTotal).toFixed(2);
        },
        sumTotalTaxes() {
            let vatTotal = 0;
            for (let i = this.items.length - 1; i >= 0; --i) {
                if (this.items[i].vatIsChecked) {
                    vatTotal += this.items[i].vat;
                }
            }

            let ttxTotal = 0;
            for (let i = this.items.length - 1; i >= 0; --i) {
                if (this.items[i].ttxIsChecked) {
                    ttxTotal += this.items[i].ttx;
                }
            }
            return ttxTotal + vatTotal;
        },
        sumGeneralTotalWithTaxes() {
            let sumGeneralTotal = 0;
            for (let i = this.items.length - 1; i >= 0; --i) {
                sumGeneralTotal += this.items[i].totalGeneralSum;
            }
            return parseFloat(sumGeneralTotal).toFixed(2);
        },
        sumQuantities() {
            let sumQuantities = Math.floor(0);
            for (let i = this.items.length - 1; i >= 0; --i) {
                sumQuantities += Math.floor(this.items[i].qty);
            }
            return sumQuantities;
        },
    },
    methods: {
        selected(item) {
            if (!item.price || item.price <= 0 || !item.qty || item.qty <= 0) {
                this.disableUpdate = true;
                this.disableZatca = true;
                this.showValidationMsg = true;
            } else {
                this.disableUpdate = false;
                this.disableZatca = false;
                this.showValidationMsg = false;
            }
            // Update Specific object with new values after changing the quantity
            let new_sub_total = item.qty * item.price;
            for (let i = this.items.length - 1; i >= 0; --i) {
                if (this.items[i].id === item.id) {
                    this.items[i].qty = item.qty;
                    this.items[i].subTotal = new_sub_total;
                    this.items[i].vat = this.unitServiceTaxInformation
                        .vat_percentage
                        ? (this.unitServiceTaxInformation.vat_percentage /
                              100) *
                          new_sub_total
                        : 0;
                    this.items[i].ttx = this.unitServiceTaxInformation
                        .tourism_percentage
                        ? (this.unitServiceTaxInformation.tourism_percentage /
                              100) *
                          new_sub_total
                        : 0;
                    this.items[i].totalGeneralSum = new_sub_total;

                    if (this.items[i].vatIsChecked) {
                        this.items[i].totalGeneralSum += this.items[i].vat;
                    }

                    if (this.items[i].ttxIsChecked) {
                        this.items[i].totalGeneralSum += this.items[i].ttx;
                    }
                }
            }
        },

        vatSliderButtonChanged(event, item) {
            let checked = event.target.checked;
            if (checked) {
                for (let i = this.items.length - 1; i >= 0; --i) {
                    if (this.items[i].id === item.id) {
                        item.vatIsChecked = true;
                        this.items[i].totalGeneralSum += this
                            .unitServiceTaxInformation.vat_percentage
                            ? (this.unitServiceTaxInformation.vat_percentage /
                                  100) *
                              item.subTotal
                            : 0;
                    }
                }
            } else {
                for (let i = this.items.length - 1; i >= 0; --i) {
                    if (this.items[i].id === item.id) {
                        item.vatIsChecked = false;
                        this.items[i].totalGeneralSum -= this.items[i].vat;
                        this.items[i].vat = this.unitServiceTaxInformation
                            .vat_percentage
                            ? (this.unitServiceTaxInformation.vat_percentage /
                                  100) *
                              item.subTotal
                            : 0;
                    }
                }
            }
        },
        ttxSliderButtonChanged(event, item) {
            let checked = event.target.checked;
            if (checked) {
                for (let i = this.items.length - 1; i >= 0; --i) {
                    if (this.items[i].id === item.id) {
                        item.ttxIsChecked = true;
                        this.items[i].totalGeneralSum += this
                            .unitServiceTaxInformation.tourism_percentage
                            ? (this.unitServiceTaxInformation
                                  .tourism_percentage /
                                  100) *
                              item.subTotal
                            : 0;
                    }
                }
            } else {
                for (let i = this.items.length - 1; i >= 0; --i) {
                    if (this.items[i].id === item.id) {
                        item.ttxIsChecked = false;
                        this.items[i].totalGeneralSum -= this.items[i].ttx;
                        this.items[i].ttx = this.unitServiceTaxInformation
                            .tourism_percentage
                            ? (this.unitServiceTaxInformation
                                  .tourism_percentage /
                                  100) *
                              item.subTotal
                            : 0;
                    }
                }
            }
        },
        editModalClosed() {
            this.disableUpdate = false;
            this.disableZatca = false;
            this.spinnerLoadOnEdit = false;
            this.showValidationMsg = false;
            this.items = [];
        },
        removeService(item) {
            this.isLoading = true;
            for (let i = this.items.length - 1; i >= 0; --i) {
                if (this.items[i].id === item.id) {
                    setTimeout(() => {
                        this.isLoading = false;
                    }, 500);
                    this.$delete(this.items, i);
                }
            }
        },
        updateServices() {
            const self = this;
            this.spinnerLoadOnEdit = true;
            this.updateInProgress = true;
            let params = {
                items: this.items,
                transaction_id: this.transaction.id,
                sumGeneralTotalWithTaxes: this.sumGeneralTotalWithTaxes,
                sumSubTotal: this.sumSubTotal,
                sumVatTotal: this.sumVatTotal,
                sumTtxTotal: this.sumTtxTotal,
                sumQuantities: this.sumQuantities,
                transaction_date: this.transaction_date,
                customer_name: this.customer_name,
                address: this.address,
                tax_number: this.tax_number,
            };
            Nova.request()
                .put("/nova-vendor/pos/update-service-transaction", params)
                .then((response) => {
                    if (response.data.status === "services-updated") {
                        self.$refs.editServiceModal.close();
                        this.spinnerLoadOnEdit = false;
                        Nova.$emit("service-transaction-updated", true);
                        this.$toasted.show(
                            this.__("Services has been updated successfully"),
                            { type: "success" }
                        );
                    }

                    if (response.data.status === "services-deleted") {
                        self.$refs.editServiceModal.close();
                        this.spinnerLoadOnEdit = false;
                        Nova.$emit("service-transaction-deleted", true);
                        this.$toasted.show(
                            this.__(
                                "Services Transaction has been deleted successfully"
                            ),
                            { type: "success" }
                        );
                    }

                    this.spinnerLoadOnEdit = false;
                    this.updateInProgress = false;

                    return false;
                })
                .catch((err) => {
                    this.spinnerLoadOnEdit = false;
                    this.updateInProgress = false;
                    if (err.response && err.response.data && err.response.data.message) {

                            this.$toasted.show(this.__(err.response.data.message), { type: 'error' });
                        } else {
                            this.$toasted.show(this.__('An error occurred while updating the transaction'), { type: 'error' });
                        }
                });
        },
        pushToZatca() {

            var invoice_type = "invoice";
            const self = this;
            this.spinnerLoadOnEditZatca = true;
            this.updateInProgress = true;
            this.disableZatca = true;

            if (this.transaction.active_note == "invoice") {
                invoice_type = "credit note";
            } else if (this.transaction.active_note == "debit note") {
                invoice_type = "credit note";
            } else if (this.transaction.active_note == "credit note") {
                invoice_type = "debit note";
            }
            let params = {
                items: this.items,
                transaction_id: this.transaction.id,
                sumGeneralTotalWithTaxes: this.sumGeneralTotalWithTaxes,
                sumSubTotal: this.sumSubTotal,
                sumVatTotal: this.sumVatTotal,
                sumTtxTotal: this.sumTtxTotal,
                sumQuantities: this.sumQuantities,
                transaction_date: this.transaction_date,
                customer_name: this.customer_name,
                address: this.address,
                tax_number: this.tax_number,
                invoice_type: invoice_type,
            };
            Nova.request()
                .put(
                    `/nova-vendor/pos/service-log/sync-invoice-to-zatca`,
                    params
                )
                .then((response) => {
                    if (
                        response.data.success == true ||
                        response.data.success == "true"
                    ) {
                        self.$refs.editServiceModal.close();
                        this.spinnerLoadOnEditZatca = false;
                        this.$toasted.show(
                            this.__(
                                "Invoice has been sent to zatca successfully"
                            ),
                            {
                                duration: 5000,
                                type: "success",
                            }
                        );
                        Nova.$emit("service-transaction-updated", true);
                    } else {
                        this.$toasted.show(
                            this.__("Invoice has not been sent to zatca"),
                            {
                                duration: 5000,
                                type: "danger",
                            }
                        );
                    }
                    this.updateInProgress = false;
                    this.spinnerLoadOnEditZatca = false;
                    this.disableZatca = false;
                })
                .catch((error) => {
                    this.updateInProgress = false;
                    this.spinnerLoadOnEditZatca = false;
                    this.disableZatca = false;
                    this.$toasted.error(error.message, {
                        duration: 3000,
                        type: "danger",
                    });
                });
        },

        checkIntegration(key) {
            this.isLoading = true;
            Nova.request()
                .get(`/nova-vendor/settings/integrations/${key}`)
                .then((response) => {
                    this.isLoading = false;
                    this.integrations = [
                        ...this.integrations,
                        {
                            key: key,
                            ...response.data,
                        },
                    ];
                })
                .catch((error) => {
                    this.isLoading = false;
                    this.$toasted.error(error, {
                        duration: 3000,
                    });
                });
        },
        ifIntegrationEnabled(key) {
            return this.integrations.find(
                (integration) =>
                    integration.key === key && integration.integration !== null
            );
        },
        checkIfSyncedWithZatca() {
            if (
                (this.transaction.meta.is_reported_to_zatca !== null ||
                    this.transaction.meta.is_reported_to_zatca_debit_note !==
                        null) &&
                this.transaction.meta.is_reported_to_zatca_credit_note == null
            ) {
                return true;
            }
            return false;
        },
        ifIssuedInvoiceZatca() {
            return (
                this.transaction.active_note == "invoice" ||
                this.transaction.active_note == "debit note" ||
                this.transaction.active_note == "credit note"
            );
        },
        ifNotIssuedInvoiceZatca() {
            return this.transaction.active_note == null;
        },
        validateOnRender() {
            //validate input field on initial rendering
            return this.ifIssuedInvoiceZatca();
        },
        getTransaction() {
            this.$parent.getData();
        },
        handleLoading($loading) {
            this.isLoading = $loading;
        },
        handleModal(toggle) {
            if (toggle) {
                this.$refs.editServiceModal.open();
            } else {
                this.$refs.editServiceModal.close();
            }
        },
        constructMeta(note) {
            const meta = {
                credit_note_number: note.type == "credit note" ? note.id : null,
                //debit_note_number: note.type == "debit note" ? note.id : null,
                invoice_type:
                    note.type == "debit note"
                        ? "Simplified Tax Invoice فاتورة ضريبية مبسطة"
                        : null,
                invoiceNumber: this.service_log_number
            };
            return meta;
        }
    },
    mounted() {
        if (Nova.app.$hasPermission("add pos date")) {
            this.canEditDate = true;
        } else {
            this.canEditDate = false;
        }

        const self = this;
        Nova.$on("transaction-date-changed", (new_date) => {
            this.transaction_date = new_date;
        });
        if (Nova.app.$hasPermission("change service price")) {
            this.canChangeServicePrice = true;
        } else {
            this.canChangeServicePrice = false;
        }

        Nova.$on("open-edit-modal", (transaction) => {
            this.isLoading = true;
            // Get taxes on services if available and cache them here into an object
            Nova.request()
                .get("/nova-vendor/calender/services-tax-info")
                .then((res) => {
                    this.unitServiceTaxInformation = res.data;
                    this.transaction = transaction;
                    for (let i = transaction.services.length - 1; i >= 0; --i) {
                        this.obj = {
                            text: transaction.services[i].statement,
                            price: Number(transaction.services[i].price),
                            qty: Math.floor(transaction.services[i].qty),
                            subTotal: transaction.services[i].sub_total,
                            vat: Number(transaction.services[i].vat),
                            ttx: Number(transaction.services[i].ttx),
                            vatIsChecked: transaction.services[i].vatIsChecked,
                            ttxIsChecked: transaction.services[i].ttxIsChecked,
                            totalGeneralSum: Number(
                                transaction.services[i].totalGeneralSum
                            ),
                            id: transaction.services[i].id,
                        };

                        this.obj.subTotal = this.obj.price * this.obj.qty;
                        this.obj.vat = this.unitServiceTaxInformation
                            .vat_percentage
                            ? this.obj.subTotal *
                              (this.unitServiceTaxInformation.vat_percentage /
                                  100)
                            : 0;
                        this.obj.ttx = this.unitServiceTaxInformation
                            .tourism_percentage
                            ? this.obj.subTotal *
                              (this.unitServiceTaxInformation
                                  .tourism_percentage /
                                  100)
                            : 0;
                        // this.obj.totalGeneralSum = this.obj.subTotal + this.obj.vat + this.obj.ttx  ;

                        if (
                            !this.items.some((item) => item.id === this.obj.id)
                        ) {
                            this.items.push(this.obj);
                        }

                        this.isLoading = false;
                    }
                });

            this.transaction_date = transaction.transaction_date;
            if (transaction.payable_type == "App\\Team") {
                this.customer_name = transaction.meta.customer_name;
                this.address = transaction.meta.address
                    ? transaction.meta.address
                    : "";
                this.tax_number = transaction.meta.tax_number
                    ? transaction.meta.tax_number
                    : "";
            } else {
                this.customer_name = "";
                this.address = "";
                this.tax_number = "";
            }

            Nova.request()
                .get(
                    `/nova-vendor/pos/service-log/zatca-einvoices/${transaction.id}`
                )
                .then((res) => {
                    this.notes = res.data.service_notes;
                    this.service_log_number = res.data.service_log_number;
                    this.loading = false;
                })
                .catch((error) => {
                    this.isLoading = false;
                    this.$toasted.error(error, {
                        duration: 3000,
                    });
                });

            setTimeout(() => {
                self.$refs.editServiceModal.open();
            }, 0);
        });

            Nova.$on("close-edit-modal", () => {
                this.$refs.editServiceModal.close();
            })
            this.checkIntegration('ZatcaPhaseTwo');

            if(Nova.app.$hasPermission('add pos date')){
                this.canEditDate = true
            } else{
                this.canEditDate = false;
            }

        }

    }

</script>

<style lang="scss" scoped>
.input_group {
    display: block;
    margin: 0 auto 15px;
    label {
        display: block;
        color: #000;
        font-size: 15px;
        text-align: initial;
        margin: 0 auto 5px;
    } /* label */
    input {
        display: block !important;
        height: 40px;
        background: #fafafa !important;
        border: 1px solid #ddd !important;
        width: 100%;
        padding: 0 10px;
        font-size: 15px;
        color: #000;
    } /* input */
} /* input_group */
</style>
<style scoped>
.form_has_wrong_inputs {
    text-align: center;
    margin-bottom: 10px;
    color: red;
    font-weight: bold;
}

.custom-edit-btn {
    background: #0a7df3;
}

.edit-service-transaction
    .addServiceinside
    .super-select-container
    label.super-select-input {
    height: 40px;
    padding: 0 10px;
}
.edit-service-transaction
    .addServiceinside
    .super-select-container
    label.super-select-input
    input {
    padding: 0;
    font-size: 16px;
    font-weight: normal;
    line-height: 40px !important;
}
.edit-service-transaction .addServiceinside .nodata {
    font-size: 16px;
    text-align: center;
    line-height: 100px;
    color: #000;
}
.edit-service-transaction .addServiceinside .table_of_services {
    margin: 20px auto 10px;
    width: 100%;
    overflow-x: auto;
    display: block;
}
.edit-service-transaction .addServiceinside .table_of_services table {
    width: 100%;
    border: 1px solid #ddd;
}
.edit-service-transaction
    .addServiceinside
    .table_of_services
    table
    thead
    tr
    th {
    background: #fafafa;
    border: 1px solid #ddd;
    padding: 10px;
    font-weight: normal;
    font-family: "Dubai-Bold";
    font-size: 14px;
    vertical-align: middle;
    white-space: normal;
}
.edit-service-transaction
    .addServiceinside
    .table_of_services
    table
    tbody
    tr
    td {
    border: 1px solid #ddd;
    text-align: center;
    padding: 10px;
    font-size: 16px;
    vertical-align: middle;
    color: #000;
    white-space: normal;
}
.edit-service-transaction
    .addServiceinside
    .table_of_services
    table
    tbody
    tr
    td
    input[type="number"] {
    border: 1px solid #ddd;
    border-radius: 5px !important;
    text-align: center;
    padding: 0;
    height: 36px;
    width: 70px;
    font-size: 20px;
    line-height: 36px;
}
.edit-service-transaction
    .addServiceinside
    .table_of_services
    table
    tbody
    tr
    td
    b {
    display: block;
    white-space: normal;
    font-weight: normal;
    word-break: break-all;
}
.edit-service-transaction
    .addServiceinside
    .table_of_services
    table
    tbody
    tr
    td:first-child {
    text-align: right;
}
.edit-service-transaction
    .addServiceinside
    .table_of_services
    table
    tbody
    tr
    td
    .checkbox
    p {
    display: inline-block;
    margin: 0 0 0 5px;
}
.edit-service-transaction
    .addServiceinside
    .table_of_services
    table
    tfoot
    tr
    td {
    padding: 10px;
    text-align: center;
    border: 1px solid #ddd;
    background: #fafafa;
    color: #000;
    font-size: 16px;
    vertical-align: middle;
}
.edit-service-transaction
    .addServiceinside
    .table_of_services
    table
    tfoot
    tr
    td
    p {
    display: block;
    margin: 5px auto 0;
}
.edit-service-transaction
    .addServiceinside
    .table_of_services
    table
    tfoot
    tr
    td
    table {
    border: none !important;
}
.edit-service-transaction
    .addServiceinside
    .table_of_services
    table
    tfoot
    tr
    td
    table
    tr
    td {
    text-align: center !important;
    border-right: none !important;
    border-top: none !important;
}
.edit-service-transaction
    .addServiceinside
    .table_of_services
    table
    tfoot
    tr
    td
    table
    tr
    td:last-child {
    border-left: none;
}
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 26px;
    margin: 5px auto;
}
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: 0.4s;
    transition: 0.4s;
}
.slider.round {
    border-radius: 34px;
}
input:checked + .slider {
    background-color: #21b978;
}
.slider::before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    -webkit-transition: 0.4s;
    transition: 0.4s;
}
.slider.round::before {
    border-radius: 50%;
}
input:checked + .slider::before {
    -webkit-transform: translateX(33px);
    transform: translateX(33px);
}
.edit-service-transaction .addServiceinside button.addbut {
    max-width: 100%;
    min-width: 50%;
    display: inline-block;
    width: auto;
    height: 40px;
    line-height: 40px;
    padding: 0 10px;
    font-size: 16px;
}

.custom-select {
    font-weight: bold;
    font-size: 16px;
    color: black;
}
.totle_cost h2 {
    font-size: 14px;
    margin: 0 auto 5px;
}
.totle_cost h4 b {
    font-weight: normal;
    font-family: Dubai-Bold;
}
/* Portrait phones and smaller */
@media (min-width: 320px) and (max-width: 480px) {
    .edit-service-transaction .addServiceinside .table_of_services {
        max-width: 100%;
        overflow: auto;
    }
    .edit-service-transaction .addServiceinside .nodata {
        font-size: 14px;
    }
    .edit-service-transaction .addServiceinside .totle_cost {
        width: 50%;
        margin: 0 0 15px;
    }
}

/* Smart phones and Tablets */
@media (min-width: 481px) and (max-width: 767px) {
    .edit-service-transaction .addServiceinside .table_of_services {
        max-width: 100%;
        overflow: auto;
    }
    .edit-service-transaction .addServiceinside .totle_cost {
        width: 50%;
        margin: 0 0 15px;
    }
}

/* Small Screens */
@media (min-width: 768px) and (max-width: 991px) {
    .edit-service-transaction .addServiceinside .table_of_services {
        max-width: 100%;
        overflow: auto;
    }
}

.spinner {
    /* Spinner size and color */
    width: 1.5rem;
    height: 1.5rem;
    border-top-color: #444;
    border-left-color: #444;

    /* Additional spinner styles */
    animation: spinner 400ms linear infinite;
    border-bottom-color: transparent;
    border-right-color: transparent;
    border-style: solid;
    border-width: 2px;
    border-radius: 50%;
    box-sizing: border-box;
    display: inline-block;
    vertical-align: middle;
}

/* Animation styles */
@keyframes spinner {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

/* Optional — create your own variations! */
.spinner-large {
    width: 5rem;
    height: 5rem;
    border-width: 6px;
}

.spinner-slow {
    animation: spinner 1s linear infinite;
}

.spinner-blue {
    border-top-color: #09d;
    border-left-color: #09d;
}
.spinner-light {
    border-top-color: #ffffff;
    border-left-color: #ffffff;
}
.gap-2 {
    gap: 10px;
}
.d-flex {
    display: flex;
}
.align-items-center {
    align-items: center;
}
.heading-3 {
    font-size: 17px;
    font-weight: 700;
    color: #bdbdbd;
}
.justify-content-center {
    justify-content: center;
}
.text-center {
    text-align: center;
}
.custom-hr {
    border-color: #d1d5db;
    width: 75%;
    justify-self: anchor-center;
    margin-top: 11px;
}
</style>
