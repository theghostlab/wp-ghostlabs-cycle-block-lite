import { useEffect, useState, useRef } from "@wordpress/element";
import {slugMaskBlur} from "../../../../../javascript/utils";
import ThreeDotsFadeIcon from "./_components/ThreeDotsFadeIcon";

export default ({value, label, help, callback, placeholder, disabled = false}) => {

    const id = `autocomplete-${slugMaskBlur(label)}`;

    const inputRef = useRef(null);

    const elClass = disabled ? `theghostlab-autocomplete disabled` : `theghostlab-autocomplete`;

     return(
         <div className={`components-base-control ${elClass}`}>
             <div className={'components-base-control__field'} style={{ position: 'relative' }} ref={inputRef}>
                 <label className={'components-base-control__label'} htmlFor={id}>{label}</label>
                 <input
                     type="text"
                     id={id}
                     className={'components-text-control__input'}
                     placeholder={placeholder}
                     onChange={e => {
                         callback(e.target.value)
                     }}
                     value={value}
                     aria-autocomplete="list"
                     disabled={disabled}
                 />
             </div>
             <p className={'components-base-control__help'} id={`inspector-text-control-${label}__help`}>{help}</p>
         </div>
     )
}