using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.IO;
using System.Text;

namespace gov.va.medora.mdws
{
    /// <summary>
    /// We can use this class to add a custom filter to our Response object. This class is 
    /// mostly a wrapper for a stream but caches the write output to the ResponseReader accessor
    /// for retrieval at the end of the request processing
    /// </summary>
    public class ResponseReader : Stream
    {
        public Int64 JsonpResponseLength { get; set; }
        public String JsonpCallback { get; set; }
        Stream _stream;
        string _responseString;
        DateTime _requestTimestamp;

        public ResponseReader(Stream stream)
        {
            _requestTimestamp = DateTime.Now;
            _stream = stream;
        }

        public DateTime RequestTimestamp { get { return _requestTimestamp; } }

        /// <summary>
        /// This custom filter caches the response value in this accessor
        /// </summary>
        public string ResponseString
        {
            get { return _responseString; }
            set { _responseString = value; }
        }

        public override bool CanRead
        {
            get { return true; }
        }

        public override bool CanSeek
        {
            get { return true; }
        }

        public override bool CanWrite
        {
            get { return true; }
        }

        public override void Flush()
        {
            _stream.Flush();
        }

        public override long Length
        {
            get { return _stream.Length; }
        }

        public override long Position
        {
            get
            {
                return _stream.Position;
            }
            set
            {
                _stream.Position = value;
            }
        }

        public override int Read(byte[] buffer, int offset, int count)
        {
            return _stream.Read(buffer, offset, count);
        }

        public override long Seek(long offset, SeekOrigin origin)
        {
            return _stream.Seek(offset, origin);
        }

        public override void SetLength(long value)
        {
            _stream.SetLength(value);
        }

        /// <summary>
        /// The overridden write converts the output to text and caches it in the ResponseString accessor. It
        /// finally converts that string back to an array of bytes and writes it back out to the wrapped stream
        /// </summary>
        /// <param name="buffer"></param>
        /// <param name="offset"></param>
        /// <param name="count"></param>
        public override void Write(byte[] buffer, int offset, int count)
        {
            _responseString = System.Text.Encoding.Default.GetString(buffer, offset, count);
            // if we found the request was a jsonp request
            if (!String.IsNullOrEmpty(this.JsonpCallback))
            {
                _responseString = String.Concat(this.JsonpCallback, "(", _responseString, ");");
                this.JsonpResponseLength = _responseString.Length;
            }
            byte[] b = System.Text.Encoding.Default.GetBytes(_responseString);
            _stream.Write(b, offset, b.Length);
        }
    }
}
