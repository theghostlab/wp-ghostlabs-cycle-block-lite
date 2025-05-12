import { Flex, FlexBlock } from '@wordpress/components';
import {__} from "@wordpress/i18n";
import NewTab from "../../../src/blocks/cycle-entry/components/icons/NewTab";
import "./style.scss"

const UtmSetButton = ({style, setLink}) => {

    return (
        <Flex style={style}>
            <FlexBlock>
                <a className={`theghostlab-cycle__button`} href={setLink} target={'_blank'}>
                    <span>{__('GET UTMs WITH PRO!', 'theghostlab')}</span>
                    <NewTab/>
                </a>
            </FlexBlock>
        </Flex>
    )
}

export default UtmSetButton;