import React, {Component} from 'react';
import {SelectList} from "./Components/SelectList";

const _ = require('lodash');
const queryString = require('query-string');

class OpportunitiesFilter extends Component {

  fields = [
    {field: 'su_opp_type', label: 'Type of Opportunity', multiple: false},
    {field: 'su_opp_time_year', label: 'When', multiple: false,},
    {field: 'su_opp_open_to', label: 'Open To', multiple: false},
    {field: 'su_opp_location', label: 'Location', multiple: true},
    {field: 'su_opp_dimension', label: 'Dimension', multiple: true},
    {field: 'su_opp_pathway', label: 'Pathway', multiple: true},
    {field: 'su_opp_placement_type', label: 'Placement Type', multiple: true},
    {field: 'su_opp_service_theme', label: 'Service Theme', multiple: true}
  ];

  constructor(props) {
    super(props);
    this.state = {
      allItems: {},
      activeItems: {},
      filters: {},
      showMoreFilters: false
    };
    this.onSelectChange = this.onSelectChange.bind(this);
    this.onFormSubmit = this.onFormSubmit.bind(this);
    this.initialFilters = [];

    if (window.location.search.length > 0) {
      this.state.filters = queryString.parse(window.location.search, {arrayFormat: 'bracket'});
      this.initialFilters = queryString.parse(window.location.search, {arrayFormat: 'bracket'});
    }
  }

  componentDidMount() {
    const that = this;
    fetch('/api/opportunties')
      .then(response => response.json())
      .then(jsonData => {
        that.setState({
          allItems: _.cloneDeep(jsonData),
          activeItems: _.cloneDeep(jsonData)
        }, this.onSelectChange)
      });
  }

  onFormSubmit(e) {
    e.preventDefault();
    if (window.location.search.length === 0 && Object.keys(this.state.filters).length === 0) {
      return;
    }

    let newLocation = window.location.pathname;
    newLocation += '?';
    Object.keys(this.state.filters).map(fieldName => {
      this.state.filters[fieldName].map(tid => {
        newLocation += `&${fieldName}[]=${tid}`;
      })
    });
    newLocation = newLocation.replace('?&', '?');
    window.location = newLocation;
  }

  onSelectChange(fieldName, selectedValues) {

    const newState = {...this.state};

    newState.activeItems = _.cloneDeep(newState.allItems);
    delete newState.filters[fieldName];
    if (selectedValues !== undefined && selectedValues.length > 0) {
      newState.filters[fieldName] = selectedValues;
    }

    Object.keys(newState.activeItems).map(fieldName => {
      let validEntities = [];

      Object.keys(newState.filters).map(filterFieldName => {
        if (fieldName === filterFieldName) {
          return;
        }

        let filterGroup = [];
        newState.filters[filterFieldName].map(selectedTid => {
          const selectedItem = newState.allItems[filterFieldName].find(a => parseInt(a.id) == parseInt(selectedTid));
          filterGroup = [...new Set(filterGroup.concat(selectedItem.items))];
        })

        if (validEntities.length === 0) {
          validEntities = [...filterGroup];
        }
        else {
          validEntities = validEntities.filter(x => filterGroup.includes(x));
        }
      })

      if (validEntities.length > 0) {
        newState.activeItems[fieldName].map(term => {
          term.items = validEntities.filter(x => term.items.includes(x));
        })
      }
    })

    this.setState(newState);
  }

  getSelectElement(field) {
    return (
      <SelectList
        key={field.field}
        label={field.label}
        field={field.field}
        onChange={this.onSelectChange}
        options={this.state.activeItems[field.field]}
        multiple={field.multiple}
        defaultValue={this.state.filters[field.field]}
      />
    )
  }

  showHideMoreFilters() {
    const newState = {...this.state};
    newState.showMoreFilters = !this.state.showMoreFilters;
    if (!newState.showMoreFilters) {
      const availableFields = this.fields.filter(field => typeof this.state.allItems[field.field] !== 'undefined' && this.state.allItems[field.field].length)
      const moreFilters = availableFields.slice(4);
      moreFilters.map(field => {
        delete newState.filters[field.field];
      })
    }
    this.setState(newState);
  }

  render() {
    const availableFields = this.fields.filter(field => typeof this.state.allItems[field.field] !== 'undefined' && this.state.allItems[field.field].length)
    const mainFilters = availableFields.slice(0, 4);
    const moreFilters = availableFields.slice(4);

    const showMoreFilter = this.state.showMoreFilters || (moreFilters.length > 0 && moreFilters.filter(field => typeof this.state.filters[field.field] !== 'undefined').length > 0);

    return (
      <div style={{margin: '20px'}}>
        <form onSubmit={this.onFormSubmit}>
          <div style={{
            display: 'flex',
            flexWrap: 'nowrap',
            justifyContent: 'space-between'
          }}>
            {mainFilters.map(field => this.getSelectElement(field))}
            {moreFilters.length > 0 &&
            <button
              type="button"
              onClick={this.showHideMoreFilters.bind(this)}>
              {this.state.showMoreFilters ? 'Less' : 'More'} Filters
            </button>
            }

          </div>
          <div style={{
            justifyContent: 'space-between',
            display: showMoreFilter ? 'flex' : 'none'
          }}>
            {moreFilters.map(field => this.getSelectElement(field))}
          </div>
          <input type="submit" value="Apply Filters"/>
          <input
            style={{marginLeft: '20px'}}
            type="submit"
            value="Reset"
            onClick={() => this.setState({filters: {}})}
          />
        </form>

        {(Object.keys(this.initialFilters).length > 0 && Object.keys(this.state.allItems).length > 0) &&
        <div className="pills">
          Showing Results for:
          {Object.keys(this.initialFilters).map(fieldName =>
            this.initialFilters[fieldName].map(termId =>
              <Pill
                key={`${fieldName}-${termId}`}
                term={this.state.allItems[fieldName].find(item => parseInt(termId) === parseInt(item.id))}
                on
              />
            )
          )}
        </div>
        }
      </div>
    )
  }
}

const Pill = ({term, onClick}) => {

  const onPillClick = (e) => {
    e.preventDefault();
    onClick(term.id);
  }

  return (
    <span style={{
      background: 'lightblue',
      borderRadius: '20px',
      padding: '5px',
      margin: '0 10px'
    }}>
      {term.label}
      <a href="#" onClick={onPillClick}>
        X<span className="visually-hidden">Remove {term.label} filter</span>
      </a>
    </span>
  )
}

ReactDOM.render(
  <OpportunitiesFilter/>,
  document.getElementById('opportunities-filter-list')
);
