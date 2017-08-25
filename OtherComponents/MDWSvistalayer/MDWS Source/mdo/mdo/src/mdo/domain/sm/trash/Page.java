package gov.va.med.mhv.sm.model;

import java.util.List;

public interface Page<T> {
	
	  boolean isFirstPage();
	  boolean isLastPage();
	  boolean hasNextPage();
	  boolean hasPreviousPage();
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
