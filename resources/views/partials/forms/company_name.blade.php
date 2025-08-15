                        <h2 class="text-2xl font-bold mb-6">Company Name</h2>
                            <x-form.input
                                placeholder="Company Name" 
                                model="company_name"
                                wire:model="company_name"
                                @class(['border-red-500'=>$errors->has('company_name')])
                            />
                            <x-form.input-error field="company_name"/>   