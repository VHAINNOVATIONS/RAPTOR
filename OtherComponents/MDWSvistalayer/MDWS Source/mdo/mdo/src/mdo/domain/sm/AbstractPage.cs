using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    public abstract class AbstractPage<T> : IPage<T>
    {
        protected int _pageSize;
        protected int _pageNumber;
        protected List<T> _elements;
        protected int _elementCount;

        public int ElementCount
        {
            get { return _elementCount; }
            set { _elementCount = value; }
        }
        public List<T> Elements
        {
            get { return _elements; }
            set { _elements = value; }
        }
        public int PageNumber
        {
            get { return _pageNumber; }
            set { _pageNumber = value; }
        }
        public int PageSize
        {
            get { return _pageSize; }
            set { _pageSize = value; }
        }

        public int getLastPageNumber()
        {
            if (_elementCount == 0) 
            { 
                return 0; 
            }
            return Convert.ToInt32(Math.Floor((double)((_elementCount - 1) / _pageSize)));
        }

        public int getNextPageNumber()
        {
            return _pageNumber + 1;
        }

        public int getPreviousPageNumber()
        {
            return _pageNumber - 1;
        }

        public int getThisPageFirstElementNumber()
        {
            return _pageNumber * _pageSize + 1;
        }

        public int getThisPageLastElementNumber()
        {
            int fullPage = getThisPageFirstElementNumber() + _pageSize - 1;
            return _elementCount < fullPage ? _elementCount : fullPage;
        }

        public bool hasNextPage()
        {
            return !isLastPage();
        }

        public bool hasPreviousPage()
        {
            return _pageNumber > 0;
        }

        public bool isFirstPage()
        {
            return _pageNumber == 0;
        }

        public bool isLastPage()
        {
            return _pageNumber >= getLastPageNumber();
        }



        public List<T> getElements()
        {
            throw new NotImplementedException();
        }

        public int getElementCount()
        {
            throw new NotImplementedException();
        }

        public void setElementCount(int total)
        {
            throw new NotImplementedException();
        }

        public void setElements(List<T> elements)
        {
            throw new NotImplementedException();
        }

        public int getPageSize()
        {
            throw new NotImplementedException();
        }

        public void setPageSize(int pageSize)
        {
            throw new NotImplementedException();
        }

        public int getPageNumber()
        {
            throw new NotImplementedException();
        }

        public void setPageNumber(int pageNumber)
        {
            throw new NotImplementedException();
        }
    }
}
