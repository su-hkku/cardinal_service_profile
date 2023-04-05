import React from 'react';
import Checkbox from '@material-ui/core/Checkbox';
import CheckBoxOutlineBlankIcon from '@material-ui/icons/CheckBoxOutlineBlank';
import CheckBoxIcon from '@material-ui/icons/CheckBox';
import styled from 'styled-components';
import { makeStyles } from '@material-ui/core/styles';

const CheckboxContainer = styled.div`
  // Styles here.
`;

export const Checkbox = ({ defaultValue, field, label, onChange, options }) => {

  const icon = <CheckBoxOutlineBlankIcon style={{ width: 18, height: 18 }} />;
  const checkedIcon = <CheckBoxIcon style={{ width: 18, height: 18, color: '#006CB8' }} />;

  return (
    <CheckboxContainer className="filter-select-container">
      <Checkbox
        className={'checkbox'}
        icon={icon}
        checkedIcon={checkedIcon}
        style={{
          marginRight: 8
        }}
        disableRipple
        checked={selected}
      />
    </CheckboxContainer>
  );
};
