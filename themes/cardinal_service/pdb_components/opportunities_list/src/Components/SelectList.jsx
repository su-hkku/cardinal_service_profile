import React from 'react';
import Checkbox from '@material-ui/core/Checkbox';
import TextField from '@material-ui/core/TextField';
import Autocomplete from '@material-ui/lab/Autocomplete';
import CheckBoxOutlineBlankIcon from '@material-ui/icons/CheckBoxOutlineBlank';
import CheckBoxIcon from '@material-ui/icons/CheckBox';
import styled from 'styled-components';

const icon = <CheckBoxOutlineBlankIcon fontSize="small" />;
const checkedIcon = <CheckBoxIcon fontSize="small" />;

const Container = styled.div`
  input,
  .MuiFilledInput-root,
  .MuiAutocomplete-root .MuiFilledInput-root.Mui-focused {
    background-color: #fff;
    box-shadow: none;

    &:hover,
    &:active,
    &:focus {
      background-color: #fff;
      box-shadow: none;
    }
  }

  .MuiAutocomplete-root .MuiFormLabel-root,
  .MuiAutocomplete-root .MuiFormLabel-root.Mui-focused {
    font-weight: 600;
    color: #4d4f53;
  }

  .MuiAutocomplete-root .MuiFilledInput-underline:after {
    border-bottom: 0px;
    border-bottom-color: none;
  }

  .MuiFilledInput-root {
    border: 1px solid #d2d3d4;
    border-top-left-radius: unset;
    border-top-right-radius: unset;
  }

  .MuiIcon-root .MuiSvgIcon-root,
  .MuiSvgIcon-fontSizeInherit {
    font-size: 18px;
    height: 18px;
    width: 18px;
  }
`;

export const SelectList = ({
  defaultValue,
  field,
  label,
  multiple,
  onChange,
  options,
}) => {
  let defaultOptions = multiple ? [] : null;
  if (defaultValue !== undefined) {
    defaultOptions = defaultValue.map((tid) => {
      return options.find((option) => option.id === tid);
    });
    defaultOptions = multiple ? defaultOptions : defaultOptions[0];
  }

  const onSelectionChange = (e, selectedItems) => {
    if (selectedItems === null) {
      onChange(field, []);
      return;
    }

    if (!multiple) {
      onChange(field, [selectedItems.id]);
      return;
    }

    const selectedOptions = selectedItems.map((item) => item.id);
    onChange(field, selectedOptions);
  };

  return (
    <Container>
      <Autocomplete
        open
        disableCloseOnSelect
        ListboxProps={{ style: { fontSize: '18px' } }}
        className={field + '-select'}
        id={field}
        options={options}
        getOptionLabel={(option) =>
          option.label + ' (' + option.items.length + ')'
        }
        getOptionDisabled={(option) => option.items.length <= 0}
        style={{ width: 300 }}
        multiple={multiple}
        onChange={onSelectionChange}
        getOptionSelected={(option, value) => option.id === value.id}
        value={defaultOptions}
        renderOption={(option, { selected }) => (
          <React.Fragment>
            <Checkbox
              className={'checkbox'}
              icon={icon}
              checkedIcon={checkedIcon}
              style={{ marginRight: 8 }}
              checked={selected}
            />
            {option.label + ' (' + option.items.length + ')'}
          </React.Fragment>
        )}
        renderInput={(params) => (
          <TextField
            autoFocus
            {...params}
            label={label}
            variant="filled"
            InputLabelProps={{ style: { marginTop: 0, fontSize: '20px' } }}
          />
        )}
      />
    </Container>
  );
};
