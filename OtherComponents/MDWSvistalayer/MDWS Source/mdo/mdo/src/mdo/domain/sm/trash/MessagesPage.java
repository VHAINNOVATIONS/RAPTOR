package gov.va.med.mhv.sm.model;

import java.io.Serializable;


public class MessagesPage extends AbstractPage<Addressee> implements Serializable{

	/**
	 * 
	 */
	private static final long serialVersionUID = -8357165776736228395L;
	public static final int DEFAULT_PAGE_SIZE = 15;
	
	
	public MessagesPage(){
		this.pageSize = DEFAULT_PAGE_SIZE;
	}
	
	public MessagesPage(int pageSize){
		this.pageSize = pageSize;
	}
		
}
