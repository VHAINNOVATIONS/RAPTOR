package gov.va.med.mhv.sm.model;

import java.util.List;

public abstract class AbstractPage<T> implements Page<T> {

	protected int pageSize;
	protected int pageNumber;
	protected List<T> elements;
	protected int elementCount;

	public int getElementCount() {
		return elementCount;
	}
	public void setElementCount(int total) {
		this.elementCount = total;
	}
	public List<T> getElements() {
		return elements;
	}
	public void setElements(List<T> elements) {
		this.elements = elements;
	}
	public int getPageNumber() {
		return pageNumber;
	}
	public void setPageNumber(int pageNumber) {
		this.pageNumber = pageNumber;
	}
	public int getPageSize() {
		return pageSize;
	}
	public void setPageSize(int pageSize) {
		this.pageSize = pageSize;
	}

	public int getLastPageNumber()
	{
		double totalResults = new Integer(elementCount).doubleValue();
		if(totalResults==0){return 0;}
		return new Double(Math.floor((totalResults-1) / pageSize)).intValue();
	}

	public int getNextPageNumber() {
		return pageNumber + 1;
	}

	public int getPreviousPageNumber() {
		return pageNumber - 1;
	}

	public int getThisPageFirstElementNumber() {
		return pageNumber * pageSize + 1;
	}

	public int getThisPageLastElementNumber() {
		int fullPage = getThisPageFirstElementNumber() + pageSize - 1;
	    return elementCount < fullPage ? elementCount : fullPage;
	}

	public boolean hasNextPage() {
		return !isLastPage();
	}

	public boolean hasPreviousPage() {
		return pageNumber > 0;
	}

	public boolean isFirstPage() {
		return pageNumber == 0;
	}

	public boolean isLastPage() {
		return pageNumber >= getLastPageNumber();
	}









}
