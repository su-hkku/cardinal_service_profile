import React from 'react';
import Checkbox from '@material-ui/core/Checkbox';
import TextField from '@material-ui/core/TextField';
import Autocomplete from '@material-ui/lab/Autocomplete';
import CheckBoxOutlineBlankIcon from '@material-ui/icons/CheckBoxOutlineBlank';
import CheckBoxIcon from '@material-ui/icons/CheckBox';
import ExpandMoreIcon from '@material-ui/icons/ExpandMore';
import styled from 'styled-components';
import { makeStyles } from '@material-ui/core/styles';

const icon = <CheckBoxOutlineBlankIcon style={{ width: 18, height: 18 }} />;
const checkedIcon = <CheckBoxIcon style={{ width: 18, height: 18 }} />;
const arrowIcon = <ExpandMoreIcon style={{ fontSize: 30 }} />;

const Container = styled.div`
  // Autocomplete Input
  .MuiAutocomplete-root {
    margin: 4px 0;
  }

  // Autocomplete TextField padding
  .MuiAutocomplete-hasPopupIcon.MuiAutocomplete-hasClearIcon
    .MuiAutocomplete-inputRoot[class*='MuiFilledInput-root'] {
    padding: 0;

    &[class*='MuiFilledInput-adornedStart'],
    &[class*='Mui-focused'] {
      padding-top: 24px;
    }
  }

  // Autocomplete Label
  .MuiFormLabel-root.MuiInputLabel-shrink,
  .MuiFormLabel-root.Mui-focused {
    font-weight: 600;
    color: #4d4f53;
  }

  // Autocomplete Chervon color
  .MuiButtonBase-root {
    color: #4d4f53;
  }

  // Autocomplete SelectList Input Slug
  .MuiInputBase-input,
  .MuiChip-root {
    font-size: 16px;
    font-weight: 400;
    line-height: normal;
    max-width: 80%;
    margin: 5px;
  }

  // Autocomplete Border-bottom
  .MuiFilledInput-underline {
    &:before,
    &:after {
      border-bottom-color: #2e2d29;
    }
  }

  // Autocomplete unset rounded corners and padding
  .MuiFilledInput-root {
    border: 1px solid #d2d3d4;
    border-top-left-radius: unset;
    border-top-right-radius: unset;
  }
`;

const useStyles = makeStyles({
  root: {
    width: '100%',
    margin: '4px 0',

    '& input': {
      backgroundColor: '#fff',
      boxShadow: 'none',
    },
  },

  focused: {
    backgroundColor: '#fff',
    boxShadow: 'none',

    '&:hover': {
      backgroundColor: '#fff',
      boxShadow: 'none',
    },

    '&:focus': {
      backgroundColor: '#fff',
      boxShadow: 'none',
    },
  },

  inputRoot: {
    border: '5px solid red',
    backgroundColor: '#fff',
    paddingTop: 0,
    fontWeight: '600',
    color: '#4d4f53',

    '&:hover': {
      backgroundColor: '#fff',
    },

    '&:focus': {
      backgroundColor: '#fff',
    },

    '&[class*="Mui-focused"]': {
      paddingTop: '24px',
      backgroundColor: '#fff',
    },
  },

  inputFocused: {
    backgroundColor: '#fff',
    paddingTop: '24px',
  },

  endAdornment: {
    top: '10px',
  },

  clearIndicator: {
    display: 'none',
  },
});

export const SelectList = ({
  defaultValue,
  field,
  label,
  multiple,
  onChange,
  options,
}) => {
  let defaultOptions = multiple ? [] : null;

  const myStyles = useStyles();

  if (defaultValue !== undefined) {
    defaultOptions = defaultValue.map((tid) =>
      options.find((option) => option.id === tid)
    );
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
        classes={myStyles}
        disableCloseOnSelect
        popupIcon={arrowIcon}
        ListboxProps={{ style: { fontSize: '18px' } }}
        className={field + '-select'}
        id={field}
        options={options}
        getOptionLabel={(option) =>
          option.label + ' (' + option.items.length + ')'
        }
        getOptionDisabled={(option) => option.items.length <= 0}
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
            {...params}
            label={label}
            variant="filled"
            InputLabelProps={{
              style: {
                marginTop: 0,
                fontSize: '20px',
              },
            }}
          />
        )}
      />
    </Container>
  );
};
