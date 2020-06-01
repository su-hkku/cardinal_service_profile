import React from 'react';
import ReactDOM from 'react-dom';
import { Filters } from '../../opportunities_list/src/Components/Filters';
import './styles.scss';

const nodeBundle = 'su_opportunity';
const nodeFields = [
  { field: 'su_opp_type', label: 'Type of Opportunity', multiple: false },
  { field: 'su_opp_open_to', label: 'Open To', multiple: false },
  { field: 'su_opp_service_theme', label: 'Service Theme', multiple: false },
  { field: 'su_opp_time_year', label: 'When', multiple: false },
  { field: 'su_opp_location', label: 'Location', multiple: false },
  { field: 'su_opp_dimension', label: 'Dimension', multiple: false },
];

const blockUuid = Object.keys(drupalSettings.pdb.configuration).find(
  (uuid) =>
    typeof drupalSettings.pdb.configuration[uuid]
      .opportunity_homepage_submit !== 'undefined'
);

ReactDOM.render(
  <Filters
    bundle={nodeBundle}
    mainFiltersCount={3}
    fields={nodeFields}
    submitUrl={
      drupalSettings.pdb.configuration[blockUuid].opportunity_homepage_submit
    }
  />,
  document.getElementById('opportunities-homepage')
);
