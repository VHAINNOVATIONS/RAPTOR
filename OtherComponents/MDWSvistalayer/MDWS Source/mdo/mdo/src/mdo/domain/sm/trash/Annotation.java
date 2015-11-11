package gov.va.med.mhv.sm.model;

import java.io.Serializable;

public class Annotation extends BaseModel implements Serializable{

	/**
	 * 
	 */
	private static final long serialVersionUID = 8266498119885616798L;
	private Thread thread;
	private String annotation;
	/* author is most probably a Clinician
	 * but I can't guarantee that there won't
	 * be a need for an administrator to annotate
	 * thread
	 */
	private User author;
	
	public Thread getThread() {
		return thread;
	}
	public void setThread(Thread thread) {
		this.thread = thread;
	}
	public String getAnnotation() {
		return annotation;
	}
	public void setAnnotation(String annotation) {
		this.annotation = annotation;
	}
	public User getAuthor() {
		return author;
	}
	public void setAuthor(User author) {
		this.author = author;
	}
	
	
}
