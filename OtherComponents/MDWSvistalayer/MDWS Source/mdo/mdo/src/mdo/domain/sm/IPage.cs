using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    public interface IPage<T>
    {
        bool isFirstPage();
        bool isLastPage();
        bool hasNextPage();
        bool hasPreviousPage();
        int getLastPageNumber();
        List<T> getElements();
        int getElementCount();
        void setElementCount(int total);

        void setElements(List<T> elements);


        int getThisPageFirstElementNumber();
        int getThisPageLastElementNumber();
        int getNextPageNumber();
        int getPreviousPageNumber();


        int getPageSize();
        void setPageSize(int pageSize);
        int getPageNumber();
        void setPageNumber(int pageNumber);

    }
}
