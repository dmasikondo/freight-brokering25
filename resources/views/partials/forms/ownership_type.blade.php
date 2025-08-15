        <div class="my-2">
            <h2 class="text-xl font-bold mb-6">Select Ownership Type</h2>
            <x-form.radio-button
                id="ownership-real"
                label="Real Owner"
                icon="shield-check"
                value="real_owner"
                model="ownership_type"
                @class([
                    'border-red-500' => $errors->has('ownership_type')
                ])
            /> 
        </div>
        <div class="my-2">         
            <x-form.radio-button
                id="ownership-broker"
                label="Broker / Agent"
                icon="exchange"
                value="broker_agent"
                model="ownership_type"
                @class([
                    'border-red-500' => $errors->has('ownership_type'),'text-blue-400'
                ])
            
            />                     
        </div> 
        <x-form.input-error field="ownership_type"/> 