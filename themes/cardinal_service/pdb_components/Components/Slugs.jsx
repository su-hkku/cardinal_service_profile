import React from 'react';
import styled from 'styled-components';

const Wrapper = styled.div`
  margin-bottom: 36px;

  h2 {
  margin-bottom: 10px;
  }
`;

const SlugWrapper = styled.div`
  display: inline-block;
  margin-right: 18px;
  width: auto;
  color: #7A7B7E;
  font-size: 24px;
`;

export const Slugs = ({ filters, terms }) => {
  return (
    <Wrapper>
      <h2>Showing Results For:</h2>
      {Object.keys(filters).map((fieldName) => {
        if (typeof terms[fieldName] !== 'undefined') {
          return filters[fieldName].map((tid) => (
            <SlugWrapper key={tid}>
              {
                terms[fieldName].find(
                  (item) => parseInt(item.id) === parseInt(tid)
                ).label
              }
            </SlugWrapper>
          ));
        }
      })}

      <a href={window.location.pathname}>Clear All <span className="visually-hidden">Filters</span></a>
    </Wrapper>
  );
};
