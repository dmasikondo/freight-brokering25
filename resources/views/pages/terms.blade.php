<x-layouts.app>
    <section id="terms-of-use" class="py-16 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-12 p-8 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-indigo-100 dark:border-indigo-900">
                <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white sm:text-5xl flex items-center justify-center">
                    <flux:icon.scale class="w-10 h-10 mr-3 text-indigo-600" />
                    Transpartner Logistics' Terms of Use
                </h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-4xl mx-auto">
                    Welcome! By using the Transpartner Logistics platform (the "Site"), you agree to be bound by these Terms of Use ("Terms"). Please review them carefully.
                </p>
            </div>
            
            <div class="space-y-4 max-w-4xl mx-auto">
                
                {{-- Loop through data (or define static sections) --}}
                @php
                    // Structure the content into an array of sections for easy rendering
                    $termsSections = [
                        ['id' => 1, 'title' => 'Introduction & Modifications', 'icon' => 'hand-raised'],
                        ['id' => 2, 'title' => 'Description of Service (Our Role)', 'icon' => 'map'],
                        ['id' => 3, 'title' => 'License and Authorization', 'icon' => 'key'],
                        ['id' => 4, 'title' => 'Carrier Selection and Ranking', 'icon' => 'truck'],
                        ['id' => 5, 'title' => 'Shipper Selection and Restrictions', 'icon' => 'lock-closed'],
                        ['id' => 6, 'title' => 'Your Obligations', 'icon' => 'clipboard-document-check'],
                        ['id' => 7, 'title' => 'Fees and Transactional Pricing', 'icon' => 'currency-dollar'],
                        ['id' => 8, 'title' => 'Site Security and Data Protection', 'icon' => 'shield-check'],
                        ['id' => 9, 'title' => 'Limitation of Liability & Warranty', 'icon' => 'exclamation-triangle'],
                        ['id' => 10, 'title' => 'Indemnification and Remedies', 'icon' => 'adjustments-horizontal'],
                        ['id' => 11, 'title' => 'Term, Termination, and Governing Law', 'icon' => 'building-library'],
                    ];
                    $openSection = 1; // Set the Introduction section to be open by default
                @endphp

                @foreach ($termsSections as $section)
                    <div x-data="{ open: {{ $section['id'] === $openSection ? 'true' : 'false' }} }">
                        {{-- HEADER BUTTON --}}
                        <button 
                            @click="open = !open" 
                            class="w-full flex justify-between items-center p-6 bg-white dark:bg-gray-800 rounded-lg shadow-md transition duration-150 ease-in-out hover:bg-indigo-50 dark:hover:bg-gray-700 focus:outline-none"
                        >
                            <span class="flex items-center text-xl font-semibold text-gray-900 dark:text-white">
                                <flux:icon :name="$section['icon']" class="mr-3 text-indigo-500" />
                                {{ $section['id'] }}. {{ $section['title'] }}
                            </span>
                            <span :class="{'transform rotate-90': open, 'transform rotate-0': !open}" class="transition-transform duration-300 text-indigo-500">
                                <flux:icon.chevron-right class="w-5 h-5" />
                            </span>
                        </button>

                        {{-- COLLAPSIBLE BODY --}}
                        <div x-show="open" x-collapse.duration.300ms class="mt-2 p-6 bg-white dark:bg-gray-800 rounded-lg shadow-inner border-l-4 border-indigo-500 dark:border-indigo-400 text-gray-700 dark:text-gray-300">
                            {{-- Placeholder for content based on ID --}}
                            @if ($section['id'] == 1)
                                <p>Welcome to <a href="http://www.transpartnerlogistics.co.zw/" class="text-indigo-600 hover:underline">Transpartner Logistics</a> (the "Site"), owned by Transpartner Logistics Pvt Ltd. ("TRANSPARTNER LOGISTICS" or "We"). By using the Site, You agree to these Terms of Use (the "Terms"). We may, in our sole discretion, modify the Terms without notice, so please review them continuously. Your continued use of the Site signifies agreement to any modifications.</p>
                            @elseif ($section['id'] == 2)
                                <p>The Site is a proprietary freight shipping platform where:</p>
                                <ul class="list-disc list-inside ml-4 space-y-2 mt-2">
                                    <li>Carriers can advertise, post rates and lanes.</li>
                                    <li>Shippers can view, price, and submit freight transportation requests.</li>
                                    <li>Shippers and carriers can enter into contractual relationships (e.g., Bills of Lading).</li>
                                </ul>
                                <p class="mt-3 font-semibold">TRANSPARTNER LOGISTICS acts as an <flux:badge color="lime">agent</flux:badge> and not as a shipper or carrier. The actual transport of goods is not part of the Services.</p>
                            @elseif ($section['id'] == 3)
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4">a. License</h4>
                                <p>TRANSPARTNER LOGISTICS grants You a limited, non-exclusive, non-transferable right to use the Site solely for the purposes outlined herein. You are prohibited from reverse engineering, disassembly, or de-compilation of the software.</p>
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4">b. Authorization</h4>
                                <p>You confirm that any individual using the Site on your behalf has the authority to bind You. <flux:badge color="lime">No one under the age of 18 may use the site.</flux:badge></p>
                            @elseif ($section['id'] == 4)
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4"> Selection and Ranking of Carriers</h4>
                                <p>To be listed, carriers must meet minimal requirements including:</p>
                                <ul class="list-disc list-inside ml-4 space-y-2 mt-2">
                                    <li>One year of uninterrupted business operations.</li>
                                    <li>At least five trucks in operation; trust and credibility.</li>
                                    <li>Pass a credit report.</li>
                                </ul>
                                <p class="mt-3">Carriers are ranked according to historical performance based on "Flags" for:<br/> (i) Cancelations; <br/>(ii) Late Pickups; and <br/>(iii) Late Deliveries.</p>
                            @elseif ($section['id'] == 5)
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4">a. Selection of Shippers</h4>
                                <p>TRANSPARTNER LOGISTICS uses commercially reasonable efforts to verify shippers' reputation. Any shipper submitting false load quotes to access carrier data will be <flux:badge color="lime">barred from the Site. </flux:badge></p>
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4">b. Restrictions</h4>
                                <p>The Site is not open to:</p>
                                <ul class="list-disc list-inside ml-4 space-y-2 mt-2">
                                    <li>Freight brokers.</li>
                                    <li>Carriers who act as a broker.</li>
                                    <li>Carriers who act as double-brokers, unless the opportunity is referred to a carrier with a written interline agreement.</li>
                                </ul>
                            @elseif ($section['id'] == 6)
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4">6. Your Obligations</h4>
                                <p>You agree to use the Service only for lawful purposes. You are responsible for all content uploaded. You shall not misrepresent affiliations, violate third-party rights, or transmit defamatory, obscene, harassing, or copyrighted material without permission.</p>
                            @elseif ($section['id'] == 7)
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4">7. Fees</h4>
                                <p>Fees paid by shippers are composed of the carrier's price and TRANSPARTNER LOGISTICS' fee. Initially, TRANSPARTNER LOGISTICS' transaction fees will be built into the price. Carriers whose system rating drops below an acceptable minimum may incur an <flux:badge color="lime">increase in the transactional fee.</flux:badge></p>
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4"> Customer Service</h4>
                                <p>Customer Service is included as part of TRANSPARTNER LOGISTICS transactional fees.</p>
                            @elseif ($section['id'] == 8)
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4">a. Site Security</h4>
                                <p>TRANSPARTNER LOGISTICS uses commercially reasonable security technologies (encryption, firewalls). You must comply with security guidelines. However, we <flux:badge color="lime">do not warrant secure operation</flux:badge> against all third-party disruptions, as we do not control the transfer of data over telecommunications facilities.</p>
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4">b. Privacy and History</h4>
                                <p>Data uploaded by carriers (e.g., rates and lanes) is <flux:badge color="lime">shared only with shippers.</flux:badge> Carrier rate data is kept confidential.</p>
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4">c. Information You Provide to Us</h4>
                                <p>Any information You provide to us or post on the Site must be <flux:badge color="lime">true, accurate, current, and complete.</flux:badge> Contact information for You or Your clients will be used solely to provide the Services.</p>
                            @elseif ($section['id'] == 9)
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4">a. Limitation of Liability (AS IS)</h4>
                                <p>You recognize that TRANSPARTNER LOGISTICS is providing the Site <flux:badge color="lime">"AS IS."</flux:badge> We are not involved in, nor responsible for, actual shipments or related services procured through the Site. We shall not be liable for the accuracy or usefulness of content, nor for any decisions made in reliance on such information.</p>
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4">b. Limited Warranty</h4>
                                <p>TRANSPARTNER LOGISTICS warrants that the Service will perform substantially in accordance with our documentation. Our <flux:badge color="lime">sole obligation </flux:badge>for a breach of warranty is to repair or replace the component. WE MAKE NO OTHER WARRANTIES WHATSOEVER, EXPRESS OR IMPLIED, AND EXPLICITLY DISCLAIM ALL WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.</p>
                            @elseif ($section['id'] == 10)
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4">10. Indemnification</h4>
                                <p>You shall defend and hold TRANSPARTNER LOGISTICS harmless against all claims, losses, costs, and attorney fees arising from or related to <flux:badge color="lime">any breach of or failure by You to perform any of your obligations</flux:badge> in these Terms.</p>
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4"> Limitation of Remedies and Liability</h4>
                                <p>Other than outstanding fees due, both parties limit their liability regarding the use and performance of the Site or Services to an amount <flux:badge color="lime">not to exceed the amount paid by You to TRANSPARTNER LOGISTICS in the immediately preceding 12 month period.</flux:badge></p>
                            @elseif ($section['id'] == 11)
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4">11. Term and Termination</h4>
                                <p>These Terms and Your license terminate upon: (i) non-payment of fees; (ii) failure to respect provisions; (iii) discontinuance of the Site; or (iv) written notice of termination by You. Termination does not relieve You of payments owing.</p>
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4">a. Force Majeure</h4>
                                <p>Neither party will be liable for performance failure or delay due to circumstances beyond its reasonable control, such as acts of war, natural disasters, or labor disruption (excluding payment obligations).</p>
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4">b. Assignment</h4>
                                <p>Client may not assign these Terms without TRANSPARTNER LOGISTICS' consent. TRANSPARTNER LOGISTICS may assign these Terms and its rights at will.</p>
                                <h4 class="font-semibold text-gray-900 dark:text-white mt-4">c. Governing Law</h4>
                                <p>These Terms shall be governed by the laws of the <flux:badge color="lime">Republic of Zimbabwe</flux:badge><br/> (i) without giving effect to its conflict of laws provisions, with jurisdiction and venue in the courts of the Republic of Zimbabwe; or <br/>(ii) for carriers whose primary service region originates in another Southern African Country, the laws of the Republic of Zimbabwe shall still govern.</p>
                            @endif
                        </div>
                    </div>
                @endforeach

            </div>
            
        </div>
    </section>
</x-layouts.app>