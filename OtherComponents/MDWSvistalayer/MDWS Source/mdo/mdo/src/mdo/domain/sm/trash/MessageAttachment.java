package gov.va.med.mhv.sm.model;

import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.Serializable;
import java.sql.Blob;
import java.sql.SQLException;

import org.hibernate.Hibernate;

public class MessageAttachment extends BaseModel implements Serializable{
	
	
     /**
	 * 
	 */
	private static final long serialVersionUID = 6686566252074341039L;
     private Blob attachment;
     private byte[] smFile;
     private String attachmentName;
     private String mimeType;
     
    
    public MessageAttachment() {
    }

    // Don't invoke this.  Used by Hibernate only. 
	 public Blob getAttachment() throws IOException 
	 {
		 if(this.smFile !=null)
			 return Hibernate.createBlob(this.smFile);
		 else
			 return null;
	 }

	
	 //	 Don't invoke this.  Used by Hibernate only. 
	 public void setAttachment(Blob attachment) throws IOException 
	 {
		 if(attachment !=null)
			 this.smFile = this.toByteArray(attachment);
	 }
	 
	
	 private byte[] toByteArray(Blob fromBlob) {
		  ByteArrayOutputStream baos = new ByteArrayOutputStream();
		  try {
		   return toByteArrayImpl(fromBlob, baos);
		  } catch (SQLException e) {
		   throw new RuntimeException(e);
		  } catch (IOException e) {
		   throw new RuntimeException(e);
		  } finally {
		   if (baos != null) {
		    try {
		     baos.close();
		    } catch (IOException ex) {
		    }
		   }
		  }
		 }

		 private byte[] toByteArrayImpl(Blob fromBlob, ByteArrayOutputStream baos)
		  throws SQLException, IOException {
		  byte[] buf = new byte[3*1024*1024];
		  InputStream is = fromBlob.getBinaryStream();
		  try {
		   for (;;) {
		    int dataSize = is.read(buf);

		    if (dataSize == -1)
		     break;
		    baos.write(buf, 0, dataSize);
		   }
		  } finally {
		   if (is != null) {
		    try {
		     is.close();
		    } catch (IOException ex) {
		    }
		   }
		  }
		  return baos.toByteArray();
		 }
		 
		 
		public byte[] getSmFile() {
			return smFile;
		}

		public void setSmFile(byte[] smFile) {
			this.smFile = smFile;
		}


		public String getAttachmentName() {
			return attachmentName;
		}


		public void setAttachmentName(String attachmentName) {
			this.attachmentName = attachmentName;
		}

		public String getMimeType() {
			return mimeType;
		}

		public void setMimeType(String mimeType) {
			this.mimeType = mimeType;
		}
}
