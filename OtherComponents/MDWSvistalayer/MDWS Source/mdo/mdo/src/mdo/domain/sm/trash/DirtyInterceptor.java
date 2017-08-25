package gov.va.med.mhv.sm.model;

import java.io.Serializable;
import java.util.Date;

import org.hibernate.EmptyInterceptor;
import org.hibernate.type.Type;


public class DirtyInterceptor extends EmptyInterceptor {

	/**
	 * 
	 */
	private static final long serialVersionUID = 396268479970309468L;


	public boolean onSave(Object entity,
            Serializable id,
            Object[] state,
            String[] propertyNames,
            Type[] types){
		((BaseModel)entity).setModifiedDate(new Date());
		return super.onSave(entity, id, state, propertyNames, types);
		
	}

	
	public boolean onFlushDirty(Object entity,
            Serializable id,
            Object[] currentState,
            Object[] previousState,
            String[] propertyNames,
            Type[] types){
		
		if(entity instanceof BaseModel){
			((BaseModel)entity).setModifiedDate(new Date());
		}
		return super.onFlushDirty(entity, id, currentState, previousState, propertyNames, types);
	}

}
