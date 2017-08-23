using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Reflection;
using System.Text;
using System.Collections;

namespace gov.va.medora.mdws.utils
{
    public class TOXmlSerializer : System.Runtime.Serialization.ISerializable
    {
        public String serialize<T>(T objectToSerialize)
        {
            return String.Concat("<object xmlns=\"http://cartweb.va.gov/meta-model\">",
                serializeRecursive(objectToSerialize),
                "</object>");
        }

        public T deserialize<T>(String stringToDeserialize)
        {
            throw new NotImplementedException();
        }

        /// <summary>
        /// The recursive serialization algorithm. Serialize all the objects base types and 
        /// recursively serialize all complex types as custom XML output
        /// </summary>
        /// <param name="levelRoot"></param>
        /// <returns></returns>
        private String serializeRecursive(Object levelRoot)
        {
            FieldInfo[] fields = levelRoot.GetType().GetFields(BindingFlags.Public | BindingFlags.Instance | BindingFlags.GetField);

            StringBuilder sb = new StringBuilder();
            foreach (FieldInfo fi in fields) // serialize base types first
            {
                if (fi.GetValue(levelRoot) == null) // don't both serializing null fields
                {
                    continue;
                }

                if (isBaseType(fi.GetValue(levelRoot)))
                {
                    sb.Append(getBaseTypeXmlString(fi, levelRoot));
                }
                else if (isCollection(fi.GetValue(levelRoot)))
                {
                    // TODO
                    ICollection collection = (ICollection)fi.GetValue(levelRoot);
                    foreach (Object collectionItem in collection)
                    {
                        // TODO
                    }
                }
                else // other complex type
                {
                    String openTag = getComplexTypeXmlOpenTag(fi, levelRoot);
                    if (!String.IsNullOrEmpty(openTag))
                    {
                        sb.Append(openTag);
                        sb.Append(serializeRecursive(fi.GetValue(levelRoot)));
                        sb.Append(getComplexTypeCloseTag());
                    }
                }
            }
            return sb.ToString();
        }

        internal String prettyPrint(String serialized)
        {
            return serialized.Replace(" /><", " />\n<");
        }

        static String XML_OBJ_CLOSE_TAG = "</object>";
        internal String getComplexTypeCloseTag()
        {
            return XML_OBJ_CLOSE_TAG;
        }

        internal bool isCollection(Object value)
        {
            return typeof(System.Collections.ICollection).IsAssignableFrom(value.GetType())
                || typeof(ICollection<>).IsAssignableFrom(value.GetType());
        }

        static String BASE_TYPE_NAMES_STRING = "System.Byte System.SByte System.Int16 System.Int32 System.Int64 System.UInt16 System.UInt32 System.UInt64 System.Single System.Double System.Boolean System.Char System.Decimal System.String";
        internal bool isBaseType(Object value)
        {
            return BASE_TYPE_NAMES_STRING.Contains(value.GetType().FullName);
        }

        internal bool isFieldBaseType(FieldInfo fi)
        {
            return BASE_TYPE_NAMES_STRING.Contains(fi.GetType().FullName);
        }

        internal String getComplexTypeXmlOpenTag(FieldInfo fi, Object parent)
        {
            // TODO - figure out what to do with ID - DON'T MAKE A GUID!!!
            return String.Format("<object N:\"{0}\" ID:\"{1}\">", fi.Name, new Guid().ToString());
        }

        internal String getBaseTypeXmlString(FieldInfo fi, Object parent)
        {
            Object fieldValue = fi.GetValue(parent);
            if (fieldValue.GetType() == typeof(System.String) && String.IsNullOrEmpty((String)fieldValue))
            {
                return String.Empty;
            }
            return String.Format("<property N:\"{0}\" V:\"{1}\" />", fi.Name, fi.GetValue(parent).ToString());
        }

        public void GetObjectData(System.Runtime.Serialization.SerializationInfo info, System.Runtime.Serialization.StreamingContext context)
        {
            throw new NotImplementedException();
        }
    }
}


 // <object xmlns="http://cartweb.va.gov/meta-model">
 // <object N:"Provider" ID:"554-37649">
 //    <property N:"name"     V:"GETHOFER,HANS"/>
 //    <property N:"SSN"      V:"111111111"/>
 //    <property N:"DUZ"      V:"37649"/>
 //    <property N:"siteId"   V:"554"/>
 //    <property N:"greeting" V:"Good afternoon HANS"/>
 // </object>
